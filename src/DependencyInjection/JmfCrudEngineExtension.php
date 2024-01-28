<?php

namespace Jmf\CrudEngine\DependencyInjection;

use Doctrine\Instantiator\Instantiator;
use Doctrine\Instantiator\InstantiatorInterface;
use Exception;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryFactory;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryFactoryInterface;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Configuration\CacheableActionConfigurationRepositoryFactory;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Routing\RouteLoader;
use Override;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Contracts\Cache\CacheInterface;

class JmfCrudEngineExtension extends Extension
{
    /**
     * @throws Exception
     */
    #[Override]
    public function load(
        array $configs,
        ContainerBuilder $container,
    ): void {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yaml');

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
                ]
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

    #[Override]
    public function getNamespace(): string
    {
        return '';
    }

    #[Override]
    public function getXsdValidationBasePath(): bool
    {
        return false;
    }

    #[Override]
    public function getAlias(): string
    {
        return 'jmf_crud_engine';
    }
}
