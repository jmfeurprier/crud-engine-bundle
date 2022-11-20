<?php

namespace Jmf\CrudEngine\Loading;

use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

abstract class EntityActionRouteLoaderBase implements EntityActionRouteLoaderInterface
{
    public function load(
        RouteCollection $routeCollection,
        string $entityClass,
        array $entityProperties
    ): void {
        $actionName = $this->getActionName();

        if (!isset($entityProperties['actions'][$actionName])) {
            return;
        }

        $actionProperties = $entityProperties['actions'][$actionName];

        $routeName = $this->getRouteName($entityProperties, $entityClass);
        $routePath = $this->getRoutePath($entityProperties, $entityClass);

        $route = new Route(
            $routePath,
            [
                '_controller'      => $this->getActionClass(),
                'entityClass'      => $entityClass,
                'actionProperties' => $actionProperties,
            ],
        );
        $route->setMethods($this->getMethods());

        $routeCollection->add(
            $routeName,
            $route
        );
    }

    abstract protected function getActionName(): string;

    /**
     * @return string[]
     */
    abstract protected function getMethods(): array;

    abstract protected function getActionClass(): string;

    private function getRouteName(
        array $entityProperties,
        string $entityClass
    ): string {
        $actionName = $this->getActionName();

        if (isset($entityProperties['actions'][$actionName]['route']['name'])) {
            return $entityProperties['actions'][$actionName]['route']['name'];
        }

        if (isset($entityProperties['name'])) {
            return "{$entityProperties['name']}.{$actionName}";
        }

        throw new CrudEngineMissingConfigurationException(
            "No CRUD entity routing name property defined for class {$entityClass} and action '{$actionName}'."
        );
    }

    private function getRoutePath(
        array $entityProperties,
        string $entityClass
    ): string {
        $actionName = $this->getActionName();

        if (isset($entityProperties['actions'][$actionName]['route']['path'])) {
            return $entityProperties['actions'][$actionName]['route']['path'];
        }

        throw new CrudEngineMissingConfigurationException(
            "No CRUD entity routing path property defined for class {$entityClass} and action '{$actionName}'."
        );
    }
}
