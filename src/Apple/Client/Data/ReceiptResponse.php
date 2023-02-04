<?php
declare(strict_types=1);

namespace Apple\Client\Data;

use Apple\Enum\ReceiptStatus;

final class ReceiptResponse
{
    private readonly ReceiptStatus $status;
    private readonly array $transactions;
    private bool $retryable;
    private string $environment;
    private bool $debug;

    public function __construct(private readonly array $payload)
    {
        $this->status = ReceiptStatus::create($payload['status']);
        $this->retryable = (bool) ($payload['is-retryable'] ?? false);
        $this->transactions = \array_map(fn(array $v) => new ReceiptTransaction($v), $payload['latest_receipt_info'] ?? []);
        $this->environment = $payload['environment'];
        $this->debug = $payload['environment'] !== 'Production';
    }

    public function getStatus(): ReceiptStatus
    {
        return $this->status;
    }

    /**
     * @return ReceiptTransaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function getLatestTransaction(): ReceiptTransaction
    {
        return $this->transactions[0];
    }

    public function isRetryable(): bool
    {
        return $this->retryable;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }
}