<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Resolution\Action;

use Jmf\CrudEngine\Model\CrudAction;
use Override;

readonly class UpdateActionConfigResolver extends ActionConfigResolverBase
{
    use RedirectsToReadDefaultTrait;

    #[Override]
    public function getActionName(): string
    {
        return CrudAction::Update->value;
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
