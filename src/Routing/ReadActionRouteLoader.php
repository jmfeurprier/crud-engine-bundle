<?php

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Controller\ReadAction;
use Override;

class ReadActionRouteLoader extends ActionRouteLoaderBase
{
    #[Override]
    public function getActionName(): string
    {
        return 'read';
    }

    #[Override]
    protected function getMethods(): iterable
    {
        return [
            'GET',
        ];
    }

    #[Override]
    protected function getActionClass(): string
    {
        return ReadAction::class;
    }
}
