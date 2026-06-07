<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation;

use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;

readonly class ConfigurationValueResolver
{
    public function __construct(
        private SchemaValueExpander $schemaValueExpander,
    ) {
    }

    /**
     * @param non-empty-string                          $value
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function resolve(
        string $value,
        array $keys,
        string $entityClass,
        string $action,
    ): string {
        return $this->schemaValueExpander->expand(
            $value,
            $keys,
            [
                'entityClass' => $entityClass,
                'action'      => $action,
            ],
        );
    }
}
