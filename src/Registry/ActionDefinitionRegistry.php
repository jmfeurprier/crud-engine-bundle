<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Registry;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Model\CrudAction;
use Override;

readonly class ActionDefinitionRegistry implements ActionDefinitionRegistryInterface
{
    /**
     * @param array<class-string, array<non-empty-string, ActionDefinition>> $definitions
     */
    public function __construct(
        private array $definitions,
    ) {
    }

    #[Override]
    public function get(
        string $entityClass,
        CrudAction $action,
    ): ActionDefinition {
        return $this->tryGet($entityClass, $action)
            ??
            throw new CrudEngineMissingConfigurationException(
                $entityClass,
                $action,
            );
    }

    #[Override]
    public function tryGet(
        string $entityClass,
        CrudAction $action,
    ): ?ActionDefinition {
        return $this->definitions[$entityClass][$action->value] ?? null;
    }

    #[Override]
    public function all(): iterable
    {
        foreach ($this->definitions as $definitionsByAction) {
            yield from $definitionsByAction;
        }
    }
}
