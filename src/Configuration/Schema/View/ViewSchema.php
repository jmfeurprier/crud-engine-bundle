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

    public const ViewFallbackMode DEFAULT_FALLBACK = ViewFallbackMode::RENDER_BUILT_IN;

    public function __construct(
        private string $path,
        private ViewVariablesSchema $variables,
        private ViewFallbackMode $viewFallbackMode,
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

    public function getViewFallbackMode(): ViewFallbackMode
    {
        return $this->viewFallbackMode;
    }
}
