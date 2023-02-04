<?php
declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Twig\Environment;
use User\Entity\User;

#[AsController()]
abstract class AbstractAction extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return [
            'twig' => Environment::class,
            'parameter_bag' => ContainerBagInterface::class,
        ];
    }

    protected function getUser(): User|null
    {
        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application. Try running "composer require symfony/security-bundle".');
        }

        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return null;
        }

        return $token->getUser();
    }
}