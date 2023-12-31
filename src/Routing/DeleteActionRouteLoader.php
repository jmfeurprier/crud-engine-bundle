<?php

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Controller\DeleteAction;
use Override;

class DeleteActionRouteLoader extends ActionRouteLoaderBase
{
    #[Override]
    public function getActionName(): string
    {
        return 'delete';
    }

    #[Override]
    protected function getMethods(): iterable
    {
        return [
            'DELETE',
        ];
    }

    #[Override]
    protected function getActionClass(): string
    {
        return DeleteAction::class;
    }
}
