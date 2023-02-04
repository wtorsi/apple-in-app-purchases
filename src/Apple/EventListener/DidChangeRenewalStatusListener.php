<?php
declare(strict_types=1);

namespace Apple\EventListener;

use Apple\Enum\NotificationSubType;
use Apple\Event\Data\TransactionInfo;
use Apple\Event\DidChangeRenewalStatus;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use User\Entity\UserSubscription;

#[AsEventListener]
class DidChangeRenewalStatusListener extends AbstractListener
{
    public function __invoke(DidChangeRenewalStatus $event): void
    {
        switch ($event->getSubType()) {
            case NotificationSubType::AUTO_RENEW_DISABLED:
                $this->updateSubscription($event, fn(UserSubscription $s) => $s->cancelSubscription());
                break;
            case NotificationSubType::AUTO_RENEW_ENABLED:
                $this->updateSubscription($event, fn(UserSubscription $s, TransactionInfo $i) => $s->prolongSubscription($i->getExpirationDatetime()));
                break;
        }
    }
}