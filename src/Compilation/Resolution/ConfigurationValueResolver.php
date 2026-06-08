<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation\Resolution;

use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Model\EntityAction;

readonly class ConfigurationValueResolver
{
    public function __construct(
        private SchemaValueExpander $schemaValueExpander,
    ) {
    }

    /**
     * @param non-empty-string                          $value
     * @param array<non-empty-string, non-empty-string> $keys
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function resolve(
        string $value,
        array $keys,
        EntityAction $entityAction,
    ): string {
        return $this->schemaValueExpander->expand(
            $value,
            $keys,
            [
                'entityClass' => $entityAction->getEntityClass(),
                'action'      => $entityAction->getAction()->value,
            ],
        );
    }
}
