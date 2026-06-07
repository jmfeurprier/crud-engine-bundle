<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Resolution\Action;

use Jmf\CrudEngine\Controller\UpdateAction;
use Override;

readonly class UpdateActionConfigResolver extends ActionConfigResolverBase
{
    use RedirectsToReadDefaultTrait;

    #[Override]
    public function getActionName(): string
    {
        return UpdateAction::ACTION;
    }

    #[Override]
    protected function getDefaultRoutePath(): string
    {
        return "{{ entitydashkeys }}/{id}/update";
    }

    #[Override]
    protected function hasForm(): bool
    {
        return true;
    }
}
