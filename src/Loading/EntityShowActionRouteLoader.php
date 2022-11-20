<?php

namespace Jmf\CrudEngine\Loading;

use Jmf\CrudEngine\Action\ShowAction;

class EntityShowActionRouteLoader extends EntityActionRouteLoaderBase
{
    protected function getActionName(): string
    {
        return 'show';
    }

    protected function getMethods(): array
    {
        return [
            'GET',
        ];
    }

    protected function getActionClass(): string
    {
        return ShowAction::class;
    }
}
