<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Definition;

use Jmf\CrudEngine\Definition\FallbackMode;

readonly class ViewDefinition
{
    /**
     * @param array<non-empty-string, list<non-empty-string>> $variables
     */
    public function __construct(
        private string $path,
        private array $variables,
        private FallbackMode $fallbackMode,
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

    public function getFallbackMode(): FallbackMode
    {
        return $this->fallbackMode;
    }
}
