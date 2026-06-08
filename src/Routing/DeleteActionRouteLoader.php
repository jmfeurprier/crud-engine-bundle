<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Controller\DeleteAction;
use Jmf\CrudEngine\Model\CrudAction;
use Override;

readonly class DeleteActionRouteLoader extends ActionRouteLoaderBase
{
    #[Override]
    public function getAction(): CrudAction
    {
        return CrudAction::Delete;
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
