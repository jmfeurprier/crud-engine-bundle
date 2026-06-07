<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Resolution\Action;

use Jmf\CrudEngine\Controller\ReadAction;
use Override;

readonly class ReadActionConfigResolver extends ActionConfigResolverBase
{
    #[Override]
    public function getActionName(): string
    {
        return ReadAction::ACTION;
    }

    #[Override]
    protected function getDefaultRoutePath(): string
    {
        return "{{ entitydashkeys }}/{id}";
    }
}
