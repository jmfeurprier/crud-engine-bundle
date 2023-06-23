<?php

namespace Jmf\CrudEngine\Loading;

use Symfony\Component\Routing\RouteCollection;

class EntityRouteLoader
{
    /**
     * @var EntityActionRouteLoaderInterface[]
     */
    private array $loaders = [];

    /**
     * @param EntityActionRouteLoaderInterface[] $loaders
     */
    public function __construct(array $loaders)
    {
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    private function addLoader(EntityActionRouteLoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }

    /**
     * @param class-string         $entityClass
     * @param array<string, mixed> $entityProperties
     */
    public function load(
        RouteCollection $routeCollection,
        string $entityClass,
        array $entityProperties
    ): void {
        foreach ($this->loaders as $loader) {
            $loader->load($routeCollection, $entityClass, $entityProperties);
        }
    }
}
