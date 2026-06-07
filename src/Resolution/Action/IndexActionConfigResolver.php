<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Resolution\Action;

use Jmf\CrudEngine\Model\CrudAction;
use Override;

readonly class IndexActionConfigResolver extends ActionConfigResolverBase
{
    #[Override]
    public function getActionName(): string
    {
        return CrudAction::Index->value;
    }

    #[Override]
    protected function getDefaultRoutePath(): string
    {
        return "{{ entitydashkeys }}";
    }
}
