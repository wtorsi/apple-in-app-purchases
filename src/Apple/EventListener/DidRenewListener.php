<?php
declare(strict_types=1);

namespace Apple\EventListener;

use Apple\Event\DidRenew;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: DidRenew::class)]
class DidRenewListener extends SubscribedListener
{
}