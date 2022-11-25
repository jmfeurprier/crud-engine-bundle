<?php

namespace Jmf\CrudEngine\Loading;

use Jmf\CrudEngine\Controller\UpdateAction;

class EntityUpdateActionRouteLoader extends EntityActionRouteLoaderBase
{
    protected function getActionName(): string
    {
        return 'update';
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
        return UpdateAction::class;
    }
}
