<?php

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Symfony\Component\Routing\RouteCollection;

interface ActionRouteLoaderInterface
{
    public function getActionName(): string;

    public function load(
        RouteCollection $routeCollection,
        ActionConfiguration $actionConfiguration,
    ): void;
}
