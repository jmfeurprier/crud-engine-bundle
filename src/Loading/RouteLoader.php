<?php

namespace Jmf\CrudEngine\Loading;

use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader implements RouteLoaderInterface
{
    private EntityRouteLoader $entityRouteLoader;

    private array $entityProperties;

    public function __construct(
        EntityRouteLoader $entityRouteLoader,
        array $entityProperties
    ) {
        $this->entityRouteLoader = $entityRouteLoader;
        $this->entityProperties  = $entityProperties;
    }

    public function load(): RouteCollection
    {
        $routeCollection = new RouteCollection();

        foreach ($this->entityProperties as $entityClass => $entityProperties) {
            $this->entityRouteLoader->load(
                $routeCollection,
                $entityClass,
                $entityProperties
            );
        }

        return $routeCollection;
    }
}
