<?php

namespace Jmf\CrudEngine\Loading;

use Jmf\CrudEngine\Controller\DeleteAction;

class EntityDeleteActionRouteLoader extends EntityActionRouteLoaderBase
{
    protected function getActionName(): string
    {
        return 'delete';
    }

    protected function getMethods(): array
    {
        return [
            'DELETE',
        ];
    }

    protected function getActionClass(): string
    {
        return DeleteAction::class;
    }
}
