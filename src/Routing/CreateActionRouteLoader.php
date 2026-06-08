<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Controller\CreateAction;
use Jmf\CrudEngine\Model\CrudAction;
use Override;

readonly class CreateActionRouteLoader extends ActionRouteLoaderBase
{
    #[Override]
    public function getAction(): CrudAction
    {
        return CrudAction::Create;
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
