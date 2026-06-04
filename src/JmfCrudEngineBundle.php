<?php

declare(strict_types=1);

namespace Jmf\CrudEngine;

use Jmf\CrudEngine\Configuration\ActionConfigurationResolverFactory;
use Jmf\CrudEngine\Configuration\Repository\ActionConfigurationRepository;
use Jmf\CrudEngine\Configuration\Repository\ActionConfigurationRepositoryFactory;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;
use Jmf\CrudEngine\Routing\RouteLoader;
use Override;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
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
     */
    #[Override]
    public function loadExtension(
        array $config,
        ContainerConfigurator $configurator,
        ContainerBuilder $container,
    ): void {
        $configurator->import('../config/services.yaml');

        $configurator->services()
            ->set(ActionHelperResolver::class)
            ->autowire()
            ->arg('$container', new Reference('service_container'))
        ;

        // The configuration is resolved once, here at container build time, and the
        // normalized result is dumped into the compiled container, so the compiled
        // container is the cache (rebuilt only on config change / cache:clear). The
        // factory hydrates that array into the repository when the service is created.
        $configurator->services()
            ->set(ActionConfigurationRepositoryFactory::class)
            ->autowire()
            ->arg('$resolvedConfigurations', $this->getResolvedConfigurations($config))
        ;

        $configurator->services()
            ->set(ActionConfigurationRepository::class)
            ->factory([service(ActionConfigurationRepositoryFactory::class), 'create'])
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
     */
    private function getResolvedConfigurations(
        array $config,
    ): array {
        return $this->actionConfigurationResolverFactory->create()->resolve($config);
    }
}
