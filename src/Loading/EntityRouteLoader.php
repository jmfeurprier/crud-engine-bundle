<?php

namespace Jmf\CrudEngine\Loading;

use Symfony\Component\Routing\RouteCollection;

class EntityRouteLoader
{
    /**
     * @var EntityActionRouteLoaderInterface[]
     */
    private array $actionLoaders = [];

    public function __construct()
    {
        // @xxx
        $this->actionLoaders = [
            new EntityCreateActionRouteLoader(),
            new EntityDeleteActionRouteLoader(),
            new EntityEditActionRouteLoader(),
            new EntityShowActionRouteLoader(),
            new EntityIndexActionRouteLoader(),
        ];
    }

    public function load(
        RouteCollection $routeCollection,
        string $entityClass,
        array $entityProperties
    ): void {
        foreach ($this->actionLoaders as $actionLoader) {
            $actionLoader->load($routeCollection, $entityClass, $entityProperties);
        }
    }
}
