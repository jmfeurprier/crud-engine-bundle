<?php

namespace Jmf\CrudEngine\Routing;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepository;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Routing\RouteCollection;
use Webmozart\Assert\Assert;

class RouteLoader implements RouteLoaderInterface
{
    /**
     * @var array<string, ActionRouteLoaderInterface>
     */
    private array $loaderByAction = [];

    /**
     * @param ActionRouteLoaderInterface[] $loaders
     */
    public function __construct(
        private readonly ActionConfigurationRepository $actionConfigurationRepository,
        iterable $loaders,
    ) {
        Assert::allIsInstanceOf($loaders, ActionRouteLoaderInterface::class);

        foreach ($loaders as $loader) {
            $this->loaderByAction[$loader->getActionName()] = $loader;
        }
    }

    public function __invoke(): RouteCollection
    {
        $routeCollection = new RouteCollection();

        foreach ($this->actionConfigurationRepository->all() as $actionConfiguration) {
            $this->loadAction($routeCollection, $actionConfiguration);
        }

        return $routeCollection;
    }

    private function loadAction(
        RouteCollection $routeCollection,
        ActionConfiguration $actionConfiguration,
    ): void {
        $loader = $this->getLoader($actionConfiguration);

        $loader->load($routeCollection, $actionConfiguration);
    }

    private function getLoader(
        ActionConfiguration $actionConfiguration,
    ): ActionRouteLoaderInterface {
        $action = $actionConfiguration->getAction();

        return $this->loaderByAction[$action] ?? throw new RuntimeException('Unsupported action.');
    }
}
