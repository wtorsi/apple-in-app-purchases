<?php
declare(strict_types=1);

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait MessageBusAwareTrait
{
    protected MessageBusInterface|null $bus = null;

    #[Required]
    public function withMessageBus(MessageBusInterface $bus): void
    {
        $this->bus = $bus;
    }
}