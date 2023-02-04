<?php
declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait EntityManagerAwareTrait
{
    protected EntityManagerInterface|null $em = null;

    #[Required]
    public function withEntityManager(EntityManagerInterface $em): void
    {
        $this->em = $em;
    }
}