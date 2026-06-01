<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\View;

readonly class ActionViewConfiguration
{
    /**
     * @param array<non-empty-string, list<non-empty-string>> $variables
     */
    public function __construct(
        private string $path,
        private array $variables,
        private ViewFallbackMode $viewFallbackMode,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array<non-empty-string, list<non-empty-string>>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getViewFallbackMode(): ViewFallbackMode
    {
        return $this->viewFallbackMode;
    }
}
