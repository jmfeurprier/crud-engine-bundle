<?php

declare(strict_types=1);

namespace Jmf\CrudEngine;

use Jmf\CrudEngine\Resolution\ActionConfigurationResolverFactory;
use Jmf\CrudEngine\Registry\ActionConfigurationRegistry;
use Jmf\CrudEngine\Registry\ActionConfigurationRegistryFactory;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperInterface;
use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineUnsupportedActionException;
use Jmf\CrudEngine\Routing\RouteLoader;
use Override;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class JmfCrudEngineBundle extends AbstractBundle
{
    protected string $extensionAlias = 'jmf_crud_engine';

    public function __construct(
        private readonly ActionConfigurationResolverFactory $actionConfigurationResolverFactory = new ActionConfigurationResolverFactory(
        ),
    ) {
    }

    #[Override]
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    /**
     * @param array<string, mixed> $config
     *
     * @throws CrudEngineConfigurationException
     * @throws CrudEngineUnsupportedActionException
     */
    #[Override]
    public function loadExtension(
        array $config,
        ContainerConfigurator $configurator,
        ContainerBuilder $container,
    ): void {
        $configurator->import('../config/services.yaml');

        // Action helpers are resolved by class name at runtime through a service locator.
        // Tagging every implementation lets them stay private (their location/visibility
        // no longer matters) instead of relying on the container as a global locator.
        $container->registerForAutoconfiguration(ActionHelperInterface::class)
            ->addTag('jmf_crud_engine.action_helper')
        ;

        // The configuration is resolved once, here at container build time, and the
        // normalized result is dumped into the compiled container, so the compiled
        // container is the cache (rebuilt only on config change / cache:clear). The
        // factory hydrates that array into the repository when the service is created.
        $configurator->services()
            ->set(ActionConfigurationRegistryFactory::class)
            ->autowire()
            ->arg('$resolvedConfigurations', $this->getResolvedConfigurations($config))
        ;

        $configurator->services()
            ->set(ActionConfigurationRegistry::class)
            ->factory([service(ActionConfigurationRegistryFactory::class), 'create'])
        ;

        $configurator->services()
            ->get(RouteLoader::class)
            ->tag('routing.route_loader')
        ;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<class-string, array<non-empty-string, array<string, mixed>>>
     *
     * @throws CrudEngineConfigurationException
     * @throws CrudEngineUnsupportedActionException
     */
    private function getResolvedConfigurations(
        array $config,
    ): array {
        return $this->actionConfigurationResolverFactory->create()->resolve($config);
    }
}
