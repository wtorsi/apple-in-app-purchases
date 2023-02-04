<?php
declare(strict_types=1);

namespace Apple\Event\Data;

/**
 * @link https://developer.apple.com/documentation/appstoreservernotifications/jwsrenewalinfodecodedpayload
 */
class RenewalInfo
{
    public function __construct(protected readonly array $payload)
    {
    }

    public function getOriginalTransactionId(): string
    {
        return $this->payload['originalTransactionId'];
    }

    public function getProductId(): string
    {
        return $this->payload['productId'];
    }
}