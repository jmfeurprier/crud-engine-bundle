<?php

namespace Jmf\CrudEngine\Loading;

use Symfony\Component\Routing\RouteCollection;

interface EntityActionRouteLoaderInterface
{
    /**
     * @param class-string         $entityClass
     * @param array<string, mixed> $entityProperties
     */
    public function load(
        RouteCollection $routeCollection,
        string $entityClass,
        array $entityProperties
    ): void;
}
