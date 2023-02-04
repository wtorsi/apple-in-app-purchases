<?php
declare(strict_types=1);

namespace Action;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use User\Entity\User;

/**
 * @method User|null getUser()
 */
class AbstractAction extends \AbstractAction
{
    public static function getSubscribedServices(): array
    {
        return [
            'parameter_bag' => ContainerBagInterface::class,
            'security.token_storage' => TokenStorageInterface::class,
        ];
    }

}