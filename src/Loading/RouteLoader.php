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
        array $entities
    ) {
        $this->entityRouteLoader = $entityRouteLoader;
        $this->entityProperties  = $entities;
    }

    public function __invoke(): RouteCollection
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
