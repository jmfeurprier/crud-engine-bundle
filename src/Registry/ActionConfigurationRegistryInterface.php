<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Registry;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;

interface ActionConfigurationRegistryInterface
{
    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     *
     * @throws CrudEngineConfigurationException
     */
    public function get(
        string $entityClass,
        string $action,
    ): ActionConfiguration;

    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     *
     * @throws CrudEngineConfigurationException
     */
    public function tryGet(
        string $entityClass,
        string $action,
    ): ?ActionConfiguration;

    /**
     * @return ActionConfiguration[]
     *
     * @throws CrudEngineConfigurationException
     */
    public function all(): iterable;
}
