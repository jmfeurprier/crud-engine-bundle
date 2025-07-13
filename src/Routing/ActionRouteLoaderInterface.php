<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
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
