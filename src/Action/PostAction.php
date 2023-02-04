<?php
declare(strict_types=1);

namespace Action;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PostAction extends AbstractAction
{
    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() + [
                'form.factory' => FormFactoryInterface::class,
                'request_stack' => RequestStack::class,
            ];
    }
}