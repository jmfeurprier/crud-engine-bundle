<?php

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Controller\CreateAction;
use Override;

class CreateActionRouteLoader extends ActionRouteLoaderBase
{
    #[Override]
    public function getActionName(): string
    {
        return 'create';
    }

    #[Override]
    protected function getMethods(): iterable
    {
        return [
            'GET',
            'POST',
        ];
    }

    #[Override]
    protected function getActionClass(): string
    {
        return CreateAction::class;
    }
}
