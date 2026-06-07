<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Resolution\Action;

use Jmf\CrudEngine\Model\CrudAction;
use Override;

readonly class DeleteActionConfigResolver extends ActionConfigResolverBase
{
    #[Override]
    public function getActionName(): string
    {
        return CrudAction::Delete->value;
    }

    #[Override]
    protected function getDefaultRoutePath(): string
    {
        return "{{ entitydashkeys }}/{id}/delete";
    }

    /**
     * @return array{route: non-empty-string, parameters: array<string, non-empty-string>}
     */
    #[Override]
    protected function getDefaultRedirection(): ?array
    {
        return [
            'route'      => "{{ entity_key }}.index",
            'parameters' => [],
        ];
    }
}
