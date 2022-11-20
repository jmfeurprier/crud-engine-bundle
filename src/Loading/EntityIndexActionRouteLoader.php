<?php

namespace Jmf\CrudEngine\Loading;

use Jmf\CrudEngine\Action\IndexAction;

class EntityIndexActionRouteLoader extends EntityActionRouteLoaderBase
{
    protected function getActionName(): string
    {
        return 'index';
    }

    protected function getMethods(): array
    {
        return [
            'GET',
        ];
    }

    protected function getActionClass(): string
    {
        return IndexAction::class;
    }
}
