<?php
declare(strict_types=1);

namespace Apple\EventListener;

use Apple\Event\Expired;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use User\Entity\UserSubscription;

#[AsEventListener]
class ExpiredListener extends AbstractListener
{
    public function __invoke(Expired $event): void
    {
        $this->updateSubscription($event, fn(UserSubscription $s) => $s->cancelSubscription());
    }
}