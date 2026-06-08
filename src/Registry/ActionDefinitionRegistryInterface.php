<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Registry;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;
use Jmf\CrudEngine\Model\CrudAction;

interface ActionDefinitionRegistryInterface
{
    /**
     * @param class-string $entityClass
     *
     * @throws CrudEngineConfigurationException
     */
    public function get(
        string $entityClass,
        CrudAction $action,
    ): ActionDefinition;

    /**
     * @param class-string $entityClass
     *
     * @throws CrudEngineConfigurationException
     */
    public function tryGet(
        string $entityClass,
        CrudAction $action,
    ): ?ActionDefinition;

    /**
     * @return ActionDefinition[]
     *
     * @throws CrudEngineConfigurationException
     */
    public function all(): iterable;
}
