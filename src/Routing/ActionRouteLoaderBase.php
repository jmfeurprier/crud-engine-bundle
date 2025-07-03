<?php

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Configuration\Action\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Override;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Webmozart\Assert\Assert;

readonly abstract class ActionRouteLoaderBase implements ActionRouteLoaderInterface
{
    #[Override]
    public function load(
        RouteCollection $routeCollection,
        ActionConfiguration $actionConfiguration,
    ): void {
        Assert::same($this->getActionName(), $actionConfiguration->getAction());

        $routeCollection->add(
            $this->getRouteName($actionConfiguration),
            $this->getRoute($actionConfiguration),
        );
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    private function getRouteName(ActionConfiguration $actionConfiguration): string
    {
        if (null !== $actionConfiguration->getRouteConfiguration()->getName()) {
            return $actionConfiguration->getRouteConfiguration()->getName();
        }

        if (null !== $actionConfiguration->getEntityName()) {
            return "{$actionConfiguration->getEntityName()}.{$this->getActionName()}";
        }

        // @todo Refine message.
        throw new CrudEngineMissingConfigurationException(
            $actionConfiguration->getEntityClass(),
            $this->getActionName(),
        );
    }

    private function getRoute(ActionConfiguration $actionConfiguration): Route
    {
        return new Route(
            path:         $this->getRoutePath($actionConfiguration),
            defaults:     [
                              '_controller' => $this->getActionClass(),
                              'entityClass' => $actionConfiguration->getEntityClass(),
                          ],
            requirements: $this->getRequirements($actionConfiguration),
            methods:      (array) $this->getMethods(),
        );
    }

    /**
     * @return string[]
     */
    abstract protected function getMethods(): iterable;

    abstract protected function getActionClass(): string;

    private function getRoutePath(ActionConfiguration $actionConfiguration): string
    {
        return $actionConfiguration->getRouteConfiguration()->getPath();
    }

    /**
     * @return array<string, string>
     */
    private function getRequirements(ActionConfiguration $actionConfiguration): array
    {
        return $actionConfiguration->getRouteConfiguration()->getRequirements()->all();
    }
}
