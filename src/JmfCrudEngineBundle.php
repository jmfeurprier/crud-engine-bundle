<?php

declare(strict_types=1);

namespace Jmf\CrudEngine;

use Jmf\CrudEngine\Configuration\ActionConfigurationRepository;
use Jmf\CrudEngine\Configuration\ActionConfigurationResolver;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;
use Jmf\CrudEngine\Routing\RouteLoader;
use Jmf\TemplateRendering\TemplateRenderer;
use Override;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

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
     *
     * @throws CrudEngineConfigurationException
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

        // The configuration is resolved once, here at container build time, and the
        // normalized result is dumped into the compiled container — so the compiled
        // container is the cache (rebuilt only on config change / cache:clear).
        $container->services()
            ->set(ActionConfigurationRepository::class)
            ->autowire()
            ->arg('$resolvedConfigurations', $this->resolveConfigurations($config))
        ;

        $container->services()
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
    private function resolveConfigurations(array $config): array
    {
        $twigEnvironment = new Environment(new ArrayLoader());
        $twigEnvironment->addExtension(new StringExtension());

        $actionConfigurationResolver = new ActionConfigurationResolver(
            new SchemaValueExpander(
                new TemplateRenderer($twigEnvironment),
            ),
        );

        return $actionConfigurationResolver->resolve($config);
    }
}
