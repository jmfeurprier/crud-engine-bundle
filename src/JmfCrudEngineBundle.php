<?php

namespace Jmf\CrudEngine;

use Doctrine\Instantiator\Instantiator;
use Doctrine\Instantiator\InstantiatorInterface;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryFactory;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryFactoryInterface;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Configuration\CacheableActionConfigurationRepositoryFactory;
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
    #[Override]
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->fixXmlConfig('entity', 'entities')
            ->children()
                ->arrayNode('entities')
                    ->info('Properties of CRUD entities.')
                    ->useAttributeAsKey('class')
                    ->arrayPrototype()
                        ->ignoreExtraKeys()
                        ->children()
                            ->scalarNode('name')->end()
                            ->arrayNode('actions')
                                ->isRequired()
                                ->useAttributeAsKey('action')
                                ->arrayPrototype()
                                    ->ignoreExtraKeys()
                                    ->children()
                                        ->scalarNode('formType')->end()
                                        ->scalarNode('helper')->end()
                                        ->arrayNode('redirection')
                                            ->children()
                                                ->scalarNode('fragment')->end()
                                                ->scalarNode('route')
                                                    ->isRequired()
                                                ->end()
                                                ->arrayNode('parameters')
                                                    ->variablePrototype()->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('route')
                                            ->children()
                                                ->scalarNode('path')->end()
                                                ->arrayNode('parameters')
                                                    ->variablePrototype()->end()
                                                ->end()
                                                ->arrayNode('requirements')
                                                    ->variablePrototype()->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('view')
                                            ->children()
                                                ->scalarNode('path')->end()
                                                ->arrayNode('variables')
                                                    ->variablePrototype()->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    #[Override]
    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $container->import('../config/services.yaml');

        $container->autowire(ActionHelperResolver::class)
            ->setArgument('$container', new Reference('service_container'))
        ;

        $container->getDefinition(RouteLoader::class)
            ->addTag('routing.route_loader')
        ;

        $container->autowire(ActionConfigurationRepositoryInterface::class)
            ->setFactory(
                [
                    new Reference(ActionConfigurationRepositoryFactoryInterface::class),
                    'make',
                ],
            )
        ;

        if (interface_exists(CacheInterface::class)) {
            $container->autowire(ActionConfigurationRepositoryFactory::class)
                ->setArgument('$config', $config['entities'])
            ;

            $container->autowire(ActionConfigurationRepositoryFactoryInterface::class)
                ->setClass(CacheableActionConfigurationRepositoryFactory::class)
                ->setArgument(
                    '$actionConfigurationRepositoryFactory',
                    new Reference(ActionConfigurationRepositoryFactory::class),
                )
            ;
        } else {
            $container->autowire(ActionConfigurationRepositoryFactoryInterface::class)
                ->setClass(ActionConfigurationRepositoryFactory::class)
                ->setArgument('$config', $config['entities'])
            ;
        }

        $container->autowire(InstantiatorInterface::class)
            ->setClass(Instantiator::class)
        ;
    }
}
