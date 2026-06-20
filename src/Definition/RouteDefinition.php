<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Definition;

readonly class RouteDefinition
{
    /**
     * @param non-empty-string                          $name
     * @param array<non-empty-string, non-empty-string> $requirements
     */
    public function __construct(
        private string $name,
        private string $path,
        private array $requirements,
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function getRequirements(): array
    {
        return $this->requirements;
    }
}
