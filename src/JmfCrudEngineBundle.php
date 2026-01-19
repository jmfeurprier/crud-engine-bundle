<?php

declare(strict_types=1);

namespace Jmf\CrudEngine;

use Jmf\CrudEngine\Configuration\ActionConfigurationRepository;
use Jmf\CrudEngine\Configuration\ActionConfigurationsLoader;
use Jmf\CrudEngine\Configuration\ActionConfigurationsLoaderInterface;
use Jmf\CrudEngine\Configuration\CacheableActionConfigurationsLoader;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Routing\RouteLoader;
use Override;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Contracts\Cache\CacheInterface;

class JmfCrudEngineBundle extends AbstractBundle
{
    protected string $extensionAlias = 'jmf_crud_engine';

    #[Override]
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    /**
     * @param array<string, mixed> $config
     */
    #[Override]
    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $container->import('../config/services.yaml');

        $container->services()
            ->set(ActionHelperResolver::class)
            ->autowire()
            ->arg('$container', new Reference('service_container'))
        ;

        $container->services()
            ->set(ActionConfigurationRepository::class)
            ->autowire()
            ->arg('$config', $config)
        ;

        $container->services()
            ->get(RouteLoader::class)
            ->tag('routing.route_loader')
        ;

        if (interface_exists(CacheInterface::class)) {
            $container->services()
                ->set(ActionConfigurationsLoader::class)
                ->autowire()
            ;

            $container->services()
                ->set(ActionConfigurationsLoaderInterface::class)
                ->class(CacheableActionConfigurationsLoader::class)
                ->autowire()
                ->arg(
                    '$actionConfigurationsLoader',
                    new Reference(ActionConfigurationsLoader::class),
                )
            ;
        } else {
            $container->services()
                ->set(ActionConfigurationsLoaderInterface::class)
                ->class(ActionConfigurationsLoader::class)
                ->autowire()
            ;
        }
    }
}
