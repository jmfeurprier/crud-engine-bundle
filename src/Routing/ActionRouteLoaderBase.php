<?php

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Override;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Webmozart\Assert\Assert;

abstract class ActionRouteLoaderBase implements ActionRouteLoaderInterface
{
    /**
     * @throws CrudEngineMissingConfigurationException
     */
    #[Override]
    public function load(
        RouteCollection $routeCollection,
        ActionConfiguration $actionConfiguration,
    ): void {
        $actionName = $this->getActionName();

        Assert::same($actionName, $actionConfiguration->getAction());

        $routeName = $this->getRouteName($actionConfiguration);
        $routePath = $this->getRoutePath($actionConfiguration);

        $route = new Route(
            path:     $routePath,
            defaults: [
                          '_controller' => $this->getActionClass(),
                          'entityClass' => $actionConfiguration->getEntityClass(),
                      ],
            methods:  (array) $this->getMethods(),
        );

        $routeCollection->add(
            $routeName,
            $route,
        );
    }

    /**
     * @return string[]
     */
    abstract protected function getMethods(): iterable;

    abstract protected function getActionClass(): string;

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    private function getRouteName(ActionConfiguration $actionConfiguration): string
    {
        $actionName = $this->getActionName();

        if (null !== $actionConfiguration->getRouteConfiguration()->getName()) {
            return $actionConfiguration->getRouteConfiguration()->getName();
        }

        if (null !== $actionConfiguration->getEntityName()) {
            return "{$actionConfiguration->getEntityName()}.{$actionName}";
        }

        $entityClass = $actionConfiguration->getEntityClass();

        throw new CrudEngineMissingConfigurationException(
            "No CRUD entity routing name property defined for class {$entityClass} and action '{$actionName}'."
        );
    }

    private function getRoutePath(ActionConfiguration $actionConfiguration): string
    {
        return $actionConfiguration->getRouteConfiguration()->getPath();
    }
}
