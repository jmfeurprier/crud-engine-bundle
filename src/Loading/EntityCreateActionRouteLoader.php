<?php

namespace Jmf\CrudEngine\Loading;

use Jmf\CrudEngine\Controller\CreateAction;

class EntityCreateActionRouteLoader extends EntityActionRouteLoaderBase
{
    protected function getActionName(): string
    {
        return 'create';
    }

    protected function getMethods(): array
    {
        return [
            'GET',
            'POST',
        ];
    }

    protected function getActionClass(): string
    {
        return CreateAction::class;
    }
}
