<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineUnsupportedActionException;
use Jmf\CrudEngine\Registry\ActionDefinitionRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Routing\RouteCollection;
use Webmozart\Assert\Assert;

readonly class RouteLoader implements RouteLoaderInterface
{
    /**
     * @var array<string, ActionRouteLoaderInterface>
     */
    private array $loaderByAction;

    /**
     * @param ActionRouteLoaderInterface[] $loaders
     */
    public function __construct(
        private ActionDefinitionRegistryInterface $actionDefinitionRegistry,
        iterable $loaders,
    ) {
        Assert::allIsInstanceOf($loaders, ActionRouteLoaderInterface::class);

        $indexed = [];

        foreach ($loaders as $loader) {
            $indexed[$loader->getAction()->value] = $loader;
        }

        $this->loaderByAction = $indexed;
    }

    /**
     * @throws CrudEngineConfigurationException
     * @throws CrudEngineUnsupportedActionException
     */
    public function __invoke(): RouteCollection
    {
        $routeCollection = new RouteCollection();

        foreach ($this->actionDefinitionRegistry->all() as $actionDefinition) {
            $this->loadAction($routeCollection, $actionDefinition);
        }

        return $routeCollection;
    }

    /**
     * @throws CrudEngineConfigurationException
     * @throws CrudEngineUnsupportedActionException
     */
    private function loadAction(
        RouteCollection $routeCollection,
        ActionDefinition $actionDefinition,
    ): void {
        $this->getLoader($actionDefinition)->load($routeCollection, $actionDefinition);
    }

    /**
     * @throws CrudEngineUnsupportedActionException
     */
    private function getLoader(
        ActionDefinition $actionDefinition,
    ): ActionRouteLoaderInterface {
        $action = $actionDefinition->getEntityAction()->getAction()->value;

        return $this->loaderByAction[$action]
            ??
            throw new CrudEngineUnsupportedActionException($actionDefinition->getEntityAction());
    }
}
