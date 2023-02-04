<?php
declare(strict_types=1);

namespace Apple\Messenger\Message;

use Api\Apple\Form\Data\ProcessReceiptDto;
use User\Entity\User;

class VerifyReceipt
{
    public function __construct(
        protected readonly string $userId,
        protected readonly string $transactionId,
        protected readonly string $productId,
        protected readonly string $receiptData
    )
    {
    }

    public static function create(User $user, ProcessReceiptDto $dto): self
    {
        return new self((string) $user->getId(), $dto->transactionId, $dto->productId, $dto->receiptData);
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getReceiptData(): string
    {
        return $this->receiptData;
    }
}