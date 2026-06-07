<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Registry;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Override;

readonly class ActionDefinitionRegistry implements ActionDefinitionRegistryInterface
{
    /**
     * @param array<class-string, array<non-empty-string, ActionDefinition>> $config
     */
    public function __construct(
        private array $config,
    ) {
    }

    #[Override]
    public function get(
        string $entityClass,
        string $action,
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
        string $action,
    ): ?ActionDefinition {
        return $this->config[$entityClass][$action] ?? null;
    }

    #[Override]
    public function all(): iterable
    {
        foreach ($this->config as $configurationsByAction) {
            yield from $configurationsByAction;
        }
    }
}
