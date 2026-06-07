<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Registry;

use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;
use Jmf\CrudEngine\Model\ActionDefinition;

interface ActionDefinitionRegistryInterface
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
    ): ActionDefinition;

    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     *
     * @throws CrudEngineConfigurationException
     */
    public function tryGet(
        string $entityClass,
        string $action,
    ): ?ActionDefinition;

    /**
     * @return ActionDefinition[]
     *
     * @throws CrudEngineConfigurationException
     */
    public function all(): iterable;
}
