<?php

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Configuration\Action\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Symfony\Component\Routing\RouteCollection;

interface ActionRouteLoaderInterface
{
    public function getActionName(): string;

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    public function load(
        RouteCollection $routeCollection,
        ActionConfiguration $actionConfiguration,
    ): void;
}
