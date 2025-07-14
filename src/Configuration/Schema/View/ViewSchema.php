<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Schema\View;

use Jmf\CrudEngine\Configuration\Schema\View\Variables\ViewVariablesSchema;

readonly class ViewSchema
{
    /**
     * @const non-empty-string
     */
    public const string DEFAULT_PATH = "{{ entity_key }}/{{ action_key }}.html.twig";

    public function __construct(
        private string $path,
        private ViewVariablesSchema $variables,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getVariables(): ViewVariablesSchema
    {
        return $this->variables;
    }
}
