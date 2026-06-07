<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
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
        Assert::same($this->getActionName(), $actionConfiguration->getEntityAction()->getAction());

        $routeCollection->add(
            $this->getRouteName($actionConfiguration),
            $this->getRoute($actionConfiguration),
        );
    }

    private function getRouteName(ActionConfiguration $actionConfiguration): string
    {
        return $actionConfiguration->getRouteConfiguration()->getName();
    }

    private function getRoute(ActionConfiguration $actionConfiguration): Route
    {
        return new Route(
            path:         $this->getRoutePath($actionConfiguration),
            defaults:     [
                              '_controller' => $this->getActionClass(),
                              'entityClass' => $actionConfiguration->getEntityAction()->getEntityClass(),
                          ],
            requirements: $this->getRequirements($actionConfiguration),
            methods:      (array) $this->getMethods(),
        );
    }

    /**
     * @return non-empty-string[]
     */
    abstract protected function getMethods(): iterable;

    abstract protected function getActionClass(): string;

    private function getRoutePath(ActionConfiguration $actionConfiguration): string
    {
        return $actionConfiguration->getRouteConfiguration()->getPath();
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    private function getRequirements(ActionConfiguration $actionConfiguration): array
    {
        return $actionConfiguration->getRouteConfiguration()->getRequirements();
    }
}
