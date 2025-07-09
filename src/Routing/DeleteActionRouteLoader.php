<?php

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Controller\DeleteAction;
use Override;

readonly class DeleteActionRouteLoader extends ActionRouteLoaderBase
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
            'GET',
            'POST',
        ];
    }

    #[Override]
    protected function getActionClass(): string
    {
        return DeleteAction::class;
    }
}
