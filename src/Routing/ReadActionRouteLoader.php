<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Controller\ReadAction;
use Jmf\CrudEngine\Model\CrudAction;
use Override;

readonly class ReadActionRouteLoader extends ActionRouteLoaderBase
{
    #[Override]
    public function getAction(): CrudAction
    {
        return CrudAction::Read;
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
