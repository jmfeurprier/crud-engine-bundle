<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation\Action;

use Jmf\CrudEngine\Model\CrudAction;
use Override;

readonly class CreateActionDefinitionCompiler extends ActionDefinitionCompilerBase
{
    #[Override]
    public function getActionName(): string
    {
        return CrudAction::Create->value;
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

    #[Override]
    protected function getDefaultRedirection(): array
    {
        return [
            'route'      => "{{ entity_key }}.read",
            'parameters' => [
                'id' => '{{ _entity.id }}',
            ],
        ];
    }
}
