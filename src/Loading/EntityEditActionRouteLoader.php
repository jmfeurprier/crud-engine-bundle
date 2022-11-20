<?php

namespace Jmf\CrudEngine\Loading;

use Jmf\CrudEngine\Action\EditAction;

class EntityEditActionRouteLoader extends EntityActionRouteLoaderBase
{
    protected function getActionName(): string
    {
        return 'edit';
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
        return EditAction::class;
    }
}
