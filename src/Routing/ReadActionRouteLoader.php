<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Controller\ReadAction;
use Override;

readonly class ReadActionRouteLoader extends ActionRouteLoaderBase
{
    #[Override]
    public function getActionName(): string
    {
        return ReadAction::ACTION;
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
