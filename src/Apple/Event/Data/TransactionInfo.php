<?php
declare(strict_types=1);

namespace Apple\Event\Data;

/**
 * @link https://developer.apple.com/documentation/appstoreservernotifications/jwstransactiondecodedpayload
 */
class TransactionInfo
{
    public function __construct(protected readonly array $payload)
    {
    }

    public function getExpirationDatetime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('@'.\ceil($this->payload['expiresDate']) / 1000);
    }

    public function getOriginalPurchaseDatetime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('@'.\ceil($this->payload['originalPurchaseDate']) / 1000);
    }

    public function getOriginalTransactionId(): string
    {
        return $this->payload['originalTransactionId'];
    }

    public function getPurchaseDatetime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('@'.\ceil($this->payload['purchaseDate']) / 1000);
    }

    public function getProductId(): string
    {
        return $this->payload['productId'];
    }

    public function getTransactionId(): string
    {
        return $this->payload['transactionId'];
    }
}