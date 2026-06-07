<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Registry;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Override;

readonly class ActionConfigurationRegistry implements ActionConfigurationRegistryInterface
{
    /**
     * @param array<class-string, array<non-empty-string, ActionConfiguration>> $config
     */
    public function __construct(
        private array $config,
    ) {
    }

    #[Override]
    public function get(
        string $entityClass,
        string $action,
    ): ActionConfiguration {
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
    ): ?ActionConfiguration {
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
