<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Resolution\Action;

use Jmf\CrudEngine\Model\CrudAction;
use Override;

readonly class ReadActionConfigResolver extends ActionConfigResolverBase
{
    #[Override]
    public function getActionName(): string
    {
        return CrudAction::Read->value;
    }

    #[Override]
    protected function getDefaultRoutePath(): string
    {
        return "{{ entitydashkeys }}/{id}";
    }
}
