<?php

namespace Jmf\CrudEngine\Loading;

use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader implements RouteLoaderInterface
{
    /**
     * @param array<class-string, array<string, mixed>> $entityProperties
     */
    public function __construct(
        private readonly EntityRouteLoader $entityRouteLoader,
        private readonly array $entityProperties
    ) {
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
