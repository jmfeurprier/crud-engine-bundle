<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Controller\IndexAction;
use Jmf\CrudEngine\Model\CrudAction;
use Override;

readonly class IndexActionRouteLoader extends ActionRouteLoaderBase
{
    #[Override]
    public function getAction(): CrudAction
    {
        return CrudAction::Index;
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
