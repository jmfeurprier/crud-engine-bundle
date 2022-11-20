<?php

namespace Jmf\CrudEngine\Loading;

use Symfony\Component\Routing\RouteCollection;

interface EntityActionRouteLoaderInterface
{
    public function load(
        RouteCollection $routeCollection,
        string $entityClass,
        array $entityProperties
    ): void;
}
