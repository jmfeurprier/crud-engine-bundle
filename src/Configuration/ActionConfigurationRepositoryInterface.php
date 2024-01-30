<?php

namespace Jmf\CrudEngine\Configuration;

use Exception;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;

interface ActionConfigurationRepositoryInterface
{
    /**
     * @param class-string $entityClass
     *
     * @throws CrudEngineMissingConfigurationException
     */
    public function get(
        string $entityClass,
        string $action,
    ): ActionConfiguration;

    /**
     * @param class-string $entityClass
     */
    public function tryGet(
        string $entityClass,
        string $action,
    ): ?ActionConfiguration;

    /**
     * @return ActionConfiguration[]
     */
    public function all(): iterable;
}
