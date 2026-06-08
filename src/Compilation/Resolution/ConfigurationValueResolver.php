<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation\Resolution;

use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Model\CrudAction;

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
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function resolve(
        string $value,
        array $keys,
        string $entityClass,
        CrudAction $action,
    ): string {
        return $this->schemaValueExpander->expand(
            $value,
            $keys,
            [
                'entityClass' => $entityClass,
                'action'      => $action->value,
            ],
        );
    }
}
