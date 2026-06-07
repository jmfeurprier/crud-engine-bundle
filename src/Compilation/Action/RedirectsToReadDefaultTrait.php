<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation\Action;

use Override;

/**
 * Default redirection shared by create and update: back to the entity's read page, carrying its id.
 */
trait RedirectsToReadDefaultTrait
{
    /**
     * @return array{route: non-empty-string, parameters: array<string, non-empty-string>}
     */
    #[Override]
    protected function getDefaultRedirection(): ?array
    {
        return [
            'route'      => "{{ entity_key }}.read",
            'parameters' => ['id' => '{{ _entity.id }}'],
        ];
    }
}
