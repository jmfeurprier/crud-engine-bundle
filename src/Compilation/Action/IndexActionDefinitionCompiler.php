<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation\Action;

use Jmf\CrudEngine\Model\CrudAction;
use Override;

readonly class IndexActionDefinitionCompiler extends ActionDefinitionCompilerBase
{
    #[Override]
    public function getAction(): CrudAction
    {
        return CrudAction::Index;
    }

    #[Override]
    protected function getDefaultRoutePath(): string
    {
        return "{{ entitydashkeys }}";
    }
}
