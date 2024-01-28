<?php

namespace Jmf\CrudEngine\Configuration;

interface ActionConfigurationRepositoryInterface
{
    /**
     * @param class-string $entityClass
     */
    public function get(
        string $entityClass,
        string $action,
    ): ActionConfiguration;

    /**
     * @return ActionConfiguration[]
     */
    public function all(): iterable;
}
