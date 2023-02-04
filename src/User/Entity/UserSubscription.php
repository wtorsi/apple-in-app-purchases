<?php
declare(strict_types=1);

namespace User\Entity;

use Api\Apple\Form\Data\ProcessReceiptDto;
use Apple\Client\Data\ReceiptResponse;
use Apple\Enum\AppleReceiptStatus;
use Apple\Messenger\Message\VerifyReceipt;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use User\Enum\SubscriptionStatus;
use User\Repository\UserSubscriptionRepository;

#[ORM\Entity(repositoryClass: UserSubscriptionRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
#[ORM\ChangeTrackingPolicy(value: 'DEFERRED_EXPLICIT')]
#[ORM\Index(columns: ['apple_transaction_id', 'apple_product_id'])]
class UserSubscription
{
    private const PENDING_IDLE_TIMEOUT = '+15 minutes';
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', unique: true, nullable: false)]
    protected ?Uuid $id;
    #[ORM\OneToOne(inversedBy: 'subscription', targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private readonly User $user;
    #[ORM\Column(type: 'smallint', nullable: false, enumType: SubscriptionStatus::class)]
    private SubscriptionStatus $status = SubscriptionStatus::INACTIVE;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private \DateTimeImmutable|null $expirationDatetime = null;
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    private string|null $appleTransactionId = null;
    #[ORM\Column(type: 'string', length: 255, unique: false, nullable: true)]
    private string|null $appleProductId = null;
    #[ORM\Column(type: 'smallint', nullable: false, enumType: AppleReceiptStatus::class)]
    private AppleReceiptStatus $appleReceiptStatus = AppleReceiptStatus::NO_ERROR;
    #[ORM\Column(type: 'smallint', nullable: true, options: ['unsigned' => true])]
    private int $retryCount = 0;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function beginAppleVerification(ProcessReceiptDto $dto): void
    {
        $this->status = SubscriptionStatus::transition($this->status, SubscriptionStatus::APPLE_PENDING);
        $this->appleTransactionId = $dto->transactionId;
        $this->appleProductId = $dto->productId;
        $this->expirationDatetime = new \DateTimeImmutable(self::PENDING_IDLE_TIMEOUT);
        $this->retryCount = 0;
    }

    public function passAppleVerification(ReceiptResponse $receipt): void
    {
        $this->status = SubscriptionStatus::transition($this->status, SubscriptionStatus::APPLE_PENDING_VERIFIED);
        $transaction = $receipt->getLatestTransaction();
        $this->appleTransactionId = $transaction->getOriginalTransactionId();
        $this->appleProductId = $transaction->getProductId();
        $this->appleReceiptStatus = AppleReceiptStatus::NO_ERROR;
        $this->expirationDatetime = $transaction->getExpirationDatetime();
    }

    public function failAppleVerification(VerifyReceipt $message, AppleReceiptStatus $status): void
    {
        $this->status = SubscriptionStatus::transition($this->status, SubscriptionStatus::APPLE_PENDING_FAILED);
        $this->appleTransactionId = $message->getTransactionId();
        $this->appleProductId = $message->getProductId();
        $this->appleReceiptStatus = $status;
        $this->expirationDatetime = null;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    public function retryAppleVerification(): void
    {
        ++$this->retryCount;
        $this->appleReceiptStatus = AppleReceiptStatus::RETRYING;
    }

    public function prolongSubscription(\DateTimeImmutable $periodEnd): void
    {
        $this->expirationDatetime = $periodEnd;
        $this->status = SubscriptionStatus::transition($this->status, SubscriptionStatus::ACTIVE);
    }

    public function cancelSubscription(\DateTimeImmutable|null $expirationDatetime = null): void
    {
        $this->appleProductId = null;
        $this->appleTransactionId = null;
        $this->expirationDatetime = $expirationDatetime ?? $this->expirationDatetime;
        $this->status = SubscriptionStatus::INACTIVE;
    }
}