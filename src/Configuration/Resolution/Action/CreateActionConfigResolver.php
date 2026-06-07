<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Resolution\Action;

use Jmf\CrudEngine\Controller\CreateAction;
use Override;

readonly class CreateActionConfigResolver extends ActionConfigResolverBase
{
    use RedirectsToReadDefaultTrait;

    #[Override]
    public function getActionName(): string
    {
        return CreateAction::ACTION;
    }

    #[Override]
    protected function getDefaultRoutePath(): string
    {
        return "{{ entitydashkeys }}/create";
    }

    #[Override]
    protected function hasForm(): bool
    {
        return true;
    }
}
