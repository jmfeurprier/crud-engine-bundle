<?php

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
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
        private ActionConfigurationRepositoryInterface $actionConfigurationRepository,
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
     * @throws CrudEngineMissingConfigurationException
     * @throws CrudEngineUnsupportedActionException
     */
    public function __invoke(): RouteCollection
    {
        $routeCollection = new RouteCollection();

        foreach ($this->actionConfigurationRepository->all() as $actionConfiguration) {
            $this->loadAction($routeCollection, $actionConfiguration);
        }

        return $routeCollection;
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     * @throws CrudEngineUnsupportedActionException
     */
    private function loadAction(
        RouteCollection $routeCollection,
        ActionConfiguration $actionConfiguration,
    ): void {
        $loader = $this->getLoader($actionConfiguration);

        $loader->load($routeCollection, $actionConfiguration);
    }

    /**
     * @throws CrudEngineUnsupportedActionException
     */
    private function getLoader(
        ActionConfiguration $actionConfiguration,
    ): ActionRouteLoaderInterface {
        $action = $actionConfiguration->getAction();

        return $this->loaderByAction[$action]
            ??
            throw new CrudEngineUnsupportedActionException($actionConfiguration);
    }
}
