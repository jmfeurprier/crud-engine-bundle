<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Controller\UpdateAction;
use Jmf\CrudEngine\Model\CrudAction;
use Override;

readonly class UpdateActionRouteLoader extends ActionRouteLoaderBase
{
    #[Override]
    public function getAction(): CrudAction
    {
        return CrudAction::Update;
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
