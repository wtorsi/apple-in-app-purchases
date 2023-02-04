<?php
declare(strict_types=1);

namespace Apple\EventListener;

use Apple\Event\AbstractEvent;
use Apple\Exception\ListenerException;
use Doctrine\DBAL\LockMode;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use User\Entity\UserSubscription;

abstract class AbstractListener
{
    use \EntityManagerAwareTrait;

    public function __construct(
        #[Autowire(service: 'monolog.logger.apple')]
        protected readonly LoggerInterface $logger
    )
    {
    }

    protected function updateSubscription(AbstractEvent $event, callable $updater): void
    {
        $this->em->beginTransaction();
        try {
            $this->em->lock($s = $this->getSubscription($event), LockMode::PESSIMISTIC_WRITE);

            \call_user_func($updater, $s, $event->getTransactionInfo());

            $this->em->persist($s);
            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    protected function getSubscription(AbstractEvent $event): UserSubscription
    {
        if (!$e = $this->em->getRepository(UserSubscription::class)->findOneByAppleTransactionInfo($event->getTransactionInfo())) {
            $this->logger->error('Could not find the User Subscription matched Apple Transaction Info', ['info' => $event->getTransactionInfo()]);
            throw new ListenerException('Could not find the User Subscription matched Apple Transaction Info');
        }
        return $e;
    }
}