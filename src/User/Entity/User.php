<?php
declare(strict_types=1);

namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use User\Repository\UserRepository;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
#[ORM\ChangeTrackingPolicy(value: 'DEFERRED_EXPLICIT')]
#[ORM\Table(name: "`user`")]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', unique: true, nullable: false)]
    protected ?Uuid $id;
    #[ORM\OneToOne(mappedBy: "user", targetEntity: UserSubscription::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    #[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
    private UserSubscription $subscription;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    private function __construct()
    {
        $this->subscription = new UserSubscription($this);
    }

    public function getSubscription(): UserSubscription
    {
        return $this->subscription;
    }

    public function getRoles(): array
    {
        // TODO: Implement getRoles() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        // TODO: Implement getUserIdentifier() method.
    }
}