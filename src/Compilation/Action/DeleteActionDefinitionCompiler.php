<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation\Action;

use Jmf\CrudEngine\Model\CrudAction;
use Override;

readonly class DeleteActionDefinitionCompiler extends ActionDefinitionCompilerBase
{
    #[Override]
    public function getAction(): CrudAction
    {
        return CrudAction::Delete;
    }

    #[Override]
    protected function getDefaultRoutePath(): string
    {
        return "{{ entitydashkeys }}/{id}/delete";
    }

    #[Override]
    protected function getDefaultRedirection(): array
    {
        return [
            'route'      => "{{ entity_key }}.index",
            'parameters' => [],
        ];
    }
}
