<?php
declare(strict_types=1);

namespace Apple\Client\Data;

final class ReceiptTransaction
{
    private readonly string $originalTransactionId;
    private readonly string $productId;
    private \DateTimeImmutable|null $expirationDatetime;

    public function __construct(array $payload)
    {
        $this->originalTransactionId = $payload['original_transaction_id'];
        $this->productId = $payload['product_id'];
        $this->expirationDatetime = new \DateTimeImmutable('@'.\ceil($payload['expires_date_ms'] / 1000));
    }

    public function getOriginalTransactionId(): string
    {
        return $this->originalTransactionId;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getExpirationDatetime(): \DateTimeImmutable
    {
        return $this->expirationDatetime;
    }
}
