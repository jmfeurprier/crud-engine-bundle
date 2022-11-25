<?php

namespace Jmf\CrudEngine\Loading;

use Jmf\CrudEngine\Controller\ReadAction;

class EntityReadActionRouteLoader extends EntityActionRouteLoaderBase
{
    protected function getActionName(): string
    {
        return 'read';
    }

    protected function getMethods(): array
    {
        return [
            'GET',
        ];
    }

    protected function getActionClass(): string
    {
        return ReadAction::class;
    }
}
