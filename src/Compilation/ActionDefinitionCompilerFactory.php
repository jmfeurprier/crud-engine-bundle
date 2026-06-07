<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation;

use Jmf\CrudEngine\Compilation\Action\CreateActionDefinitionCompiler;
use Jmf\CrudEngine\Compilation\Action\DeleteActionDefinitionCompiler;
use Jmf\CrudEngine\Compilation\Action\IndexActionDefinitionCompiler;
use Jmf\CrudEngine\Compilation\Action\ReadActionDefinitionCompiler;
use Jmf\CrudEngine\Compilation\Action\UpdateActionDefinitionCompiler;
use Jmf\CrudEngine\Compilation\ConfigurationValueResolver;
use Jmf\CrudEngine\Compilation\FormDefinitionCompiler;
use Jmf\CrudEngine\Compilation\MapResolver;
use Jmf\CrudEngine\Compilation\OverridableConfigurationValueResolver;
use Jmf\CrudEngine\Compilation\PatternsResolver;
use Jmf\CrudEngine\Compilation\RedirectionDefinitionCompiler;
use Jmf\CrudEngine\Compilation\RouteDefinitionCompiler;
use Jmf\CrudEngine\Compilation\SchemaValueExpander;
use Jmf\CrudEngine\Compilation\ViewDefinitionCompiler;
use Jmf\TemplateRendering\TemplateRenderer;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

/**
 * Builds an {@see ActionDefinitionCompiler} (and its per-concern part resolvers) with a
 * self-contained placeholder expander (a minimal Twig environment), for use at container
 * build time where no DI services exist.
 */
final readonly class ActionDefinitionCompilerFactory
{
    public function create(): ActionDefinitionCompiler
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

        $formDefinitionCompiler = new FormDefinitionCompiler(
            $configurationValueResolver,
            $mapResolver,
            $patternsResolver,
        );

        $routeDefinitionCompiler = new RouteDefinitionCompiler(
            $configurationValueResolver,
            $mapResolver,
            $overridableConfigurationValueResolver,
        );

        $redirectionDefinitionCompiler = new RedirectionDefinitionCompiler(
            $configurationValueResolver,
            $mapResolver,
        );

        $viewDefinitionCompiler = new ViewDefinitionCompiler(
            $configurationValueResolver,
            $mapResolver,
            $overridableConfigurationValueResolver,
        );

        $partResolvers = [
            $configurationValueResolver,
            $mapResolver,
            $patternsResolver,
            $formDefinitionCompiler,
            $routeDefinitionCompiler,
            $redirectionDefinitionCompiler,
            $viewDefinitionCompiler,
        ];

        return new ActionDefinitionCompiler(
            $mapResolver,
            [
                new CreateActionDefinitionCompiler(...$partResolvers),
                new DeleteActionDefinitionCompiler(...$partResolvers),
                new IndexActionDefinitionCompiler(...$partResolvers),
                new ReadActionDefinitionCompiler(...$partResolvers),
                new UpdateActionDefinitionCompiler(...$partResolvers),
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
