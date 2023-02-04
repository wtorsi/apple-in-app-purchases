<?php
declare(strict_types=1);

namespace Apple\Token;

use Firebase\JWT\JWT;
use Security\Token\EncoderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class Encoder implements EncoderInterface
{
    private const AUDIENCE = 'appstoreconnect-v1';

    public function __construct(
        #[Autowire('%env(APPLE_API_ISSUER_ID)%')]
        private readonly string $issuerId,
        #[Autowire('%env(APPLE_BUNDLE_ID)%')]
        private readonly string $bundleId,
        #[Autowire('%env(APPLE_API_PRIVATE_KEY_ID)%')]
        private readonly string $privateKeyId,
        #[Autowire('%env(APPLE_API_PRIVATE_KEY)%')]
        private readonly string $privateKey,
        private readonly string $algorithm = 'ES256'
    )
    {
    }

    public function encode(array $data = []): string
    {
        return JWT::encode([
            "iss" => $this->issuerId,
            "iat" => \time(),
            "exp" => \time() + 3600,
            "aud" => self::AUDIENCE,
            "bid" => $this->bundleId,
        ], $this->privateKey, $this->algorithm, $this->privateKeyId);
    }
}