<?php
declare(strict_types=1);

namespace Apple\Token;

use Apple\Exception\Token\BeforeValidTokenException;
use Apple\Exception\Token\DecodeFailureException;
use Apple\Exception\Token\ExpiredTokenException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Security\Token\DecoderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class Decoder implements DecoderInterface
{
    private const APPLE_CERTIFICATE_FINGERPRINTS = [
        // Fingerprint of https://www.apple.com/certificateauthority/AppleWWDRCAG6.cer
        '0be38bfe21fd434d8cc51cbe0e2bc7758ddbf97b',
        // Fingerprint of https://www.apple.com/certificateauthority/AppleRootCA-G3.cer
        'b52cb02fd567e0359fe8fa4d4c41037970fe01b0',

    ];
    private const CHAIN_LENGTH = 3;
    private const SANDBOX_ENV = 'Sandbox';
    private const ENV = 'Production';
    private readonly string $env;

    public function __construct(
        #[Autowire('%env(APPLE_BUNDLE_ID)%')]
        private readonly string $bundleId,
        #[Autowire('%env(bool:APPLE_DEBUG)%')]
        private readonly bool $debug,
    )
    {
        $this->env = $this->debug ? self::SANDBOX_ENV : self::ENV;
    }

    public function decode(string $token): array
    {
        $payload = $this->parse($token, $key = $this->getKey($token));

        if (($bundleId = $payload['data']['bundleId'] ?? '') !== $this->bundleId) {
            throw new DecodeFailureException(\sprintf('Invalid "bundleId" key provided "%s" expected "%s"', $bundleId, $this->bundleId));
        }

        if (($env = $payload['data']['environment'] ?? '') !== $this->env) {
            throw new DecodeFailureException(\sprintf('Invalid "environment" key provided "%s" expected "%s"', $env, $this->env));
        }

        foreach (['notificationType', 'notificationUUID'] as $k) {
            if (!($payload[$k] ?? '')) {
                throw new DecodeFailureException(\sprintf('Invalid "%s" key provided "%s" expected "%s"', $k, $env, $this->env));
            }
        }

        foreach (['signedRenewalInfo', 'signedTransactionInfo'] as $k) {
            if (isset($payload['data'][$k])) {
                $payload['data'][$k] = $this->parse($payload['data'][$k], $key);
            }
        }

        return $payload;
    }

    private function parse(string $data, Key $key): array
    {
        try {
            return $this->toArray((array) JWT::decode($data, $key));
        } catch (ExpiredException $e) {
            throw new ExpiredTokenException($e->getMessage(), $e);
        } catch (BeforeValidException $e) {
            throw new BeforeValidTokenException($e->getMessage(), $e);
        } catch (SignatureInvalidException $e) {
            throw new \Apple\Exception\Token\SignatureInvalidException($e->getMessage(), $e);
        } catch (\UnexpectedValueException $e) {
            throw new DecodeFailureException($e->getMessage(), $e);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Could not parse JWS string', 0, $e);
        }
    }

    private function toArray(array $data): array
    {
        return \array_map(fn($v) => $v instanceof \stdClass || \is_array($v) ? $this->toArray((array) $v) : $v, $data);
    }

    /**
     * @return \OpenSSLCertificate[]
     */
    private function chain(array $certificates): array
    {
        return \array_map(fn(string $s) => $this->base64DerToCert($s), $certificates);
    }

    private function base64DerToCert(string $certificate): \OpenSSLCertificate|null
    {
        return \openssl_x509_read(
            '-----BEGIN CERTIFICATE-----'.PHP_EOL.
            $certificate.PHP_EOL.
            '-----END CERTIFICATE-----'
        ) ?: null;
    }

    private function getKey(string $token): Key
    {
        try {
            $header = JWT::jsonDecode(JWT::urlsafeB64Decode(\explode('.', $token)[0]));
        } catch (\Throwable $e) {
            throw new DecodeFailureException('Could not decode header.', $e);
        }

        if (\count($x5c = $header->x5c ?? []) !== self::CHAIN_LENGTH) {
            throw new DecodeFailureException('Invalid x5c header.');
        }

        [$leaf, $intermediate, $root] = $this->chain($x5c);

        if (self::APPLE_CERTIFICATE_FINGERPRINTS !== [\openssl_x509_fingerprint($intermediate), \openssl_x509_fingerprint($root)]) {
            throw new DecodeFailureException('Header does not match Apple certificates');
        }

        foreach ([[$leaf, $intermediate], [$intermediate, $root]] as [$cert, $pkey]) {
            if (\openssl_x509_verify($cert, $pkey) !== 1) {
                throw new DecodeFailureException('Header certificates do not ');
            }
        }

        return new Key(\openssl_pkey_get_public($leaf), $header->alg);
    }
}