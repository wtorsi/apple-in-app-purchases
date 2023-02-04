<?php
declare(strict_types=1);

namespace Apple\Messenger\Handler;

use Apple\Client\AppleClient;
use Apple\Client\Data\ReceiptResponse;
use Apple\Enum\AppleReceiptStatus;
use Apple\Messenger\Message\VerifyReceipt;
use Doctrine\DBAL\LockMode;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use User\Entity\User;
use User\Entity\UserSubscription;

#[AsMessageHandler]
class VerifyReceiptHandler
{
    use \EntityManagerAwareTrait;

    private const MAX_RETRY_COUNT = 5;

    public function __construct(
        private readonly AppleClient $client,
        #[Autowire('%env(bool:APPLE_DEBUG)%')]
        private readonly bool $debug,
        #[Autowire(service: 'monolog.logger.apple')]
        private readonly LoggerInterface $logger,
    )
    {
    }

    /**
     * @throws \Throwable
     */
    public function __invoke(VerifyReceipt $message): void
    {
        if (!$this->isStatusValid($receipt = $this->getReceipt($message))) {
            if ($this->isStatusRetryable($receipt)) {
                $this->canRetry($message) ? $this->retry($message, $receipt) : $this->fail($message, $receipt, AppleReceiptStatus::MAX_RETRY_REACHED);
                return;
            }

            $this->fail($message, $receipt, AppleReceiptStatus::ERROR);
            return;
        }

        if (!$this->isEnvValid($receipt)) {
            $this->fail($message, $receipt, AppleReceiptStatus::INVALID_ENV);
            return;
        }

        $this->pass($message, $receipt);
    }

    private function getReceipt(VerifyReceipt $message): ReceiptResponse
    {
        try {
            return $this->client->getReceipt($message->getReceiptData());
        } catch (\Throwable $e) {
            $this->logger->error('Could not fetch the valid Apple receipt, retry', [
                'user' => $message->getUserId(),
                'transaction_id' => $message->getTransactionId(),
                'product_id' => $message->getProductId(),
                'error' => $e,
            ]);
            throw new RecoverableMessageHandlingException();
        }
    }

    /**
     * @throws \Throwable
     */
    private function retry(VerifyReceipt $message, ReceiptResponse $receipt): void
    {
        $this->logger->error('Temporary error occurred while processing Apple Verification Receipt, retry', [
            'user' => $message->getUserId(),
            'transaction_id' => $message->getTransactionId(),
            'product_id' => $message->getProductId(),
            'status' => $receipt->getStatus(),
            'payload' => $receipt->getPayload(),
        ]);

        $this->update($message, fn(UserSubscription $s) => $s->retryAppleVerification());

        throw new RecoverableMessageHandlingException();
    }

    /**
     * @throws \Throwable
     */
    private function fail(VerifyReceipt $message, ReceiptResponse $receipt, AppleReceiptStatus $status): void
    {
        $this->logger->error('Permanent error occurred while processing Apple Verification Receipt', [
            'reason' => $status,
            'user' => $message->getUserId(),
            'transaction_id' => $message->getTransactionId(),
            'product_id' => $message->getProductId(),
            'status' => $receipt->getStatus(),
            'payload' => $receipt->getPayload(),
        ]);

        $this->update($message, fn(UserSubscription $s) => $s->failAppleVerification($message, $status));
    }

    /**
     * @throws \Throwable
     */
    private function pass(VerifyReceipt $message, ReceiptResponse $receipt): void
    {
        $this->logger->info('Receipt passed Apple Verification ', [
            'user' => $message->getUserId(),
            'transaction_id' => $message->getTransactionId(),
            'product_id' => $message->getProductId(),
            'receipt' => $receipt,
        ]);

        $this->update($message, fn(UserSubscription $s) => $s->passAppleVerification($receipt));
    }

    private function getUser(VerifyReceipt $message): User
    {
        if (!$u = $this->em->find(User::class, $message->getUserId())) {
            $this->logger->error('Could not get user', ['user' => $message->getUserId()]);
            throw new UnrecoverableMessageHandlingException();
        }
        return $u;
    }

    private function getSubscription(VerifyReceipt $message): UserSubscription
    {
        return $this->getUser($message)->getSubscription();
    }

    private function update(VerifyReceipt $message, \Closure $updater): void
    {
        $this->em->beginTransaction();
        try {
            $this->em->lock($subscription = $this->getSubscription($message), LockMode::PESSIMISTIC_WRITE);
            \call_user_func($updater, $subscription);
            $this->em->persist($subscription);
            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    private function canRetry(VerifyReceipt $message): bool
    {
        if ($this->getSubscription($message)->getRetryCount() >= self::MAX_RETRY_COUNT) {
            $this->logger->error('Max retry count was reached, fail.', [
                'user' => $message->getUserId(),
                'transaction_id' => $message->getTransactionId(),
                'product_id' => $message->getProductId(),
            ]);
            return false;
        }
        return true;
    }

    private function isStatusValid(ReceiptResponse $receipt): bool
    {
        return $receipt->getStatus()->isValid();
    }

    private function isStatusRetryable(ReceiptResponse $receipt): bool
    {
        return ($receipt->getStatus()->isTemporary() && $receipt->isRetryable()) || $receipt->getStatus()->isTemporary();
    }

    private function isEnvValid(ReceiptResponse $receipt): bool
    {
        return $receipt->isDebug() === $this->debug;
    }
}
