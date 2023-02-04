<?php
declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait LoggerAwareTrait
{
    protected LoggerInterface|null $logger = null;

    #[Required]
    public function withLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}