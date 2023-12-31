<?php

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Controller\UpdateAction;
use Override;

class UpdateActionRouteLoader extends ActionRouteLoaderBase
{
    #[Override]
    public function getActionName(): string
    {
        return 'update';
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
        return UpdateAction::class;
    }
}
