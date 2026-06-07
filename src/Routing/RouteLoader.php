<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Registry\ActionConfigurationRegistryInterface;
use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineUnsupportedActionException;
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
        private ActionConfigurationRegistryInterface $actionConfigurationRegistry,
        iterable $loaders,
    ) {
        Assert::allIsInstanceOf($loaders, ActionRouteLoaderInterface::class);

        $indexed = [];

        foreach ($loaders as $loader) {
            $indexed[$loader->getActionName()] = $loader;
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

        foreach ($this->actionConfigurationRegistry->all() as $actionConfiguration) {
            $this->loadAction($routeCollection, $actionConfiguration);
        }

        return $routeCollection;
    }

    /**
     * @throws CrudEngineConfigurationException
     * @throws CrudEngineUnsupportedActionException
     */
    private function loadAction(
        RouteCollection $routeCollection,
        ActionConfiguration $actionConfiguration,
    ): void {
        $this->getLoader($actionConfiguration)->load($routeCollection, $actionConfiguration);
    }

    /**
     * @throws CrudEngineUnsupportedActionException
     */
    private function getLoader(
        ActionConfiguration $actionConfiguration,
    ): ActionRouteLoaderInterface {
        $action = $actionConfiguration->getEntityAction()->getAction();

        return $this->loaderByAction[$action]
            ??
            throw CrudEngineUnsupportedActionException::forActionConfiguration($actionConfiguration);
    }
}
