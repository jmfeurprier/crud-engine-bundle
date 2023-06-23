<?php

namespace Jmf\CrudEngine\DependencyInjection;

use Doctrine\Instantiator\Instantiator;
use Doctrine\Instantiator\InstantiatorInterface;
use Exception;
use Jmf\CrudEngine\Loading\RouteLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class JmfCrudEngineExtension extends Extension
{
    /**
     * @throws Exception
     */
    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yaml');

        $container->autowire(RouteLoader::class)
            ->setArgument('$entityProperties', $config['entities'])
            ->addTag('routing.route_loader')
        ;

        $container->autowire(InstantiatorInterface::class)
            ->setClass(Instantiator::class)
        ;
    }

    public function getNamespace(): string
    {
        return '';
    }

    public function getXsdValidationBasePath(): bool
    {
        return false;
    }

    public function getAlias(): string
    {
        return 'jmf_crud_engine';
    }
}
