<?php
declare(strict_types=1);

namespace Apple\EventListener;

use Apple\Event\AbstractEvent;
use Apple\Event\Data\TransactionInfo;
use Apple\Event\Subscribed;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use User\Entity\UserSubscription;

#[AsEventListener(event: Subscribed::class)]
class SubscribedListener extends AbstractListener
{
    public function __invoke(AbstractEvent $event): void
    {
        $this->updateSubscription(
            $event,
            fn(UserSubscription $s, TransactionInfo $i) => $s->prolongSubscription($i->getExpirationDatetime())
        );
    }
}