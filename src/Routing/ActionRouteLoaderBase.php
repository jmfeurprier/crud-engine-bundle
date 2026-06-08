<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Override;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Webmozart\Assert\Assert;

readonly abstract class ActionRouteLoaderBase implements ActionRouteLoaderInterface
{
    #[Override]
    public function load(
        RouteCollection $routeCollection,
        ActionDefinition $actionDefinition,
    ): void {
        Assert::same($this->getAction(), $actionDefinition->getEntityAction()->getAction());

        $routeCollection->add(
            $this->getRouteName($actionDefinition),
            $this->getRoute($actionDefinition),
        );
    }

    private function getRouteName(ActionDefinition $actionDefinition): string
    {
        return $actionDefinition->getRouteDefinition()->getName();
    }

    private function getRoute(ActionDefinition $actionDefinition): Route
    {
        return new Route(
            path:         $this->getRoutePath($actionDefinition),
            defaults:     [
                              '_controller' => $this->getActionClass(),
                              'entityClass' => $actionDefinition->getEntityAction()->getEntityClass(),
                          ],
            requirements: $this->getRequirements($actionDefinition),
            methods:      (array) $this->getMethods(),
        );
    }

    /**
     * @return non-empty-string[]
     */
    abstract protected function getMethods(): iterable;

    abstract protected function getActionClass(): string;

    private function getRoutePath(ActionDefinition $actionDefinition): string
    {
        return $actionDefinition->getRouteDefinition()->getPath();
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    private function getRequirements(ActionDefinition $actionDefinition): array
    {
        return $actionDefinition->getRouteDefinition()->getRequirements();
    }
}
