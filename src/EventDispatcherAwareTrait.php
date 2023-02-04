<?php
declare(strict_types=1);

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait EventDispatcherAwareTrait
{
    protected EventDispatcherInterface|null $dispatcher = null;

    #[Required]
    public function withDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }
}