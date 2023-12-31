<?php

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Controller\IndexAction;
use Override;

class IndexActionRouteLoader extends ActionRouteLoaderBase
{
    #[Override]
    public function getActionName(): string
    {
        return 'index';
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
        return IndexAction::class;
    }
}
