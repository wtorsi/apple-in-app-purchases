<?php
declare(strict_types=1);

namespace Apple\Client;

use Apple\Client\Data\ReceiptResponse;
use Apple\Enum\ReceiptStatus;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AppleClient
{
    private const URL = 'https://buy.itunes.apple.com/verifyReceipt';
    private const SANDBOX_URL = 'https://sandbox.itunes.apple.com/verifyReceipt';

    public function __construct(
        private readonly HttpClientInterface $client,
        #[Autowire('%env(APPLE_SHARED_SECRET)%')]
        private readonly string $secret,
        #[Autowire(service: 'monolog.logger.apple')]
        private readonly LoggerInterface $logger
    )
    {
        if ($this->logger instanceof LoggerAwareInterface) {
            $this->logger->setLogger($this->logger);
        }
    }

    public function getReceipt(string $data): ReceiptResponse
    {
        try {
            $response = $this->request(self::URL, $data);
            if (ReceiptStatus::create($response['status'])->isSandboxEnv()) {
                $response = $this->request(self::SANDBOX_URL, $data);
            }
            return new ReceiptResponse($response);
        } catch (\Throwable $e) {
            $this->logger->error('Could not fetch the receipt from Apple', ['error' => $e]);
            throw $e;
        }
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function request(string $url, string $data): array
    {
        $response = $this->client
            ->request('POST', $url, [
                'json' => [
                    'receipt-data' => $data,
                    'password' => $this->secret,
                    'exclude-old-transactions' => true,
                ],
            ]);

        $data = $response->toArray();
        if (!isset($data['status'])) {
            $this->logger->error('Missed "status" parameter in the Apple Verify response');
            throw new ServerException($response);
        }

        if (ReceiptStatus::create($data['status'])->isRuntimeError()) {
            $this->logger->error('The request to the Apple Verify endpoint is invalid', ['status' => $data['status']]);
            throw new ServerException($response);
        }

        return $data;
    }
}