<?php
declare(strict_types=1);

namespace Action;

use Symfony\Component\HttpFoundation\RequestStack;

class GetAction extends AbstractAction
{
    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() + [
                'request_stack' => RequestStack::class,
            ];
    }
}