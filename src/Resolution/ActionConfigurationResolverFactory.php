<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Resolution;

use Jmf\CrudEngine\Resolution\Action\CreateActionConfigResolver;
use Jmf\CrudEngine\Resolution\Action\DeleteActionConfigResolver;
use Jmf\CrudEngine\Resolution\Action\IndexActionConfigResolver;
use Jmf\CrudEngine\Resolution\Action\ReadActionConfigResolver;
use Jmf\CrudEngine\Resolution\Action\UpdateActionConfigResolver;
use Jmf\CrudEngine\Resolution\ConfigurationValueResolver;
use Jmf\CrudEngine\Resolution\FormConfigurationResolver;
use Jmf\CrudEngine\Resolution\MapResolver;
use Jmf\CrudEngine\Resolution\OverridableConfigurationValueResolver;
use Jmf\CrudEngine\Resolution\PatternsResolver;
use Jmf\CrudEngine\Resolution\RedirectionConfigurationResolver;
use Jmf\CrudEngine\Resolution\RouteConfigurationResolver;
use Jmf\CrudEngine\Resolution\SchemaValueExpander;
use Jmf\CrudEngine\Resolution\ViewConfigurationResolver;
use Jmf\TemplateRendering\TemplateRenderer;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

/**
 * Builds an {@see ActionConfigurationResolver} (and its per-concern part resolvers) with a
 * self-contained placeholder expander (a minimal Twig environment), for use at container
 * build time where no DI services exist.
 */
final readonly class ActionConfigurationResolverFactory
{
    public function create(): ActionConfigurationResolver
    {
        $mapResolver         = new MapResolver();
        $patternsResolver    = new PatternsResolver();
        $schemaValueExpander = $this->getSchemaValueExpander();

        $overridableConfigurationValueResolver = new OverridableConfigurationValueResolver(
            $schemaValueExpander,
        );

        $configurationValueResolver = new ConfigurationValueResolver(
            $schemaValueExpander,
        );

        $formConfigurationResolver = new FormConfigurationResolver(
            $configurationValueResolver,
            $mapResolver,
            $patternsResolver,
        );

        $routeConfigurationResolver = new RouteConfigurationResolver(
            $configurationValueResolver,
            $mapResolver,
            $overridableConfigurationValueResolver,
        );

        $redirectionConfigurationResolver = new RedirectionConfigurationResolver(
            $configurationValueResolver,
            $mapResolver,
        );

        $viewConfigurationResolver = new ViewConfigurationResolver(
            $configurationValueResolver,
            $mapResolver,
            $overridableConfigurationValueResolver,
        );

        $partResolvers = [
            $configurationValueResolver,
            $mapResolver,
            $patternsResolver,
            $formConfigurationResolver,
            $routeConfigurationResolver,
            $redirectionConfigurationResolver,
            $viewConfigurationResolver,
        ];

        return new ActionConfigurationResolver(
            $mapResolver,
            [
                new CreateActionConfigResolver(...$partResolvers),
                new DeleteActionConfigResolver(...$partResolvers),
                new IndexActionConfigResolver(...$partResolvers),
                new ReadActionConfigResolver(...$partResolvers),
                new UpdateActionConfigResolver(...$partResolvers),
            ],
        );
    }

    private function getSchemaValueExpander(): SchemaValueExpander
    {
        return new SchemaValueExpander(
            $this->getTemplateRenderer(),
        );
    }

    private function getTemplateRenderer(): TemplateRendererInterface
    {
        return new TemplateRenderer(
            $this->getTwigEnvironment(),
        );
    }

    private function getTwigEnvironment(): Environment
    {
        $twigEnvironment = new Environment(new ArrayLoader());
        $twigEnvironment->addExtension(new StringExtension());

        return $twigEnvironment;
    }
}
