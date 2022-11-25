<?php

namespace Jmf\CrudEngine\DependencyInjection;

use Jmf\CrudEngine\Loading\RouteLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class JmfCrudEngineExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(
        array $configs,
        ContainerBuilder $containerBuilder
    ) {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $containerBuilder,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yml');

        $containerBuilder->autowire(RouteLoader::class)
            ->setArgument('$entities', $config['entities'])
            ->addTag('routing.route_loader')
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
