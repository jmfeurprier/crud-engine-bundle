<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation\Action;

use Jmf\CrudEngine\Model\CrudAction;
use Override;

readonly class UpdateActionDefinitionCompiler extends ActionDefinitionCompilerBase
{
    #[Override]
    public function getAction(): CrudAction
    {
        return CrudAction::Update;
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
