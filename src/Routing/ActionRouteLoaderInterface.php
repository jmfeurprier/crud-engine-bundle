<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Model\CrudAction;
use Symfony\Component\Routing\RouteCollection;

interface ActionRouteLoaderInterface
{
    public function getAction(): CrudAction;

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    public function load(
        RouteCollection $routeCollection,
        ActionDefinition $actionDefinition,
    ): void;
}
