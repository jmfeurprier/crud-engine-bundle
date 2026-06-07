<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Resolution\Action;

use Jmf\CrudEngine\Controller\IndexAction;
use Override;

readonly class IndexActionConfigResolver extends ActionConfigResolverBase
{
    #[Override]
    public function getActionName(): string
    {
        return IndexAction::ACTION;
    }

    #[Override]
    protected function getDefaultRoutePath(): string
    {
        return "{{ entitydashkeys }}";
    }
}
