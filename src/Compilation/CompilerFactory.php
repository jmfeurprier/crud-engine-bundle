<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation;

use Jmf\CrudEngine\Compilation\Action\CreateActionDefinitionCompiler;
use Jmf\CrudEngine\Compilation\Action\DeleteActionDefinitionCompiler;
use Jmf\CrudEngine\Compilation\Action\IndexActionDefinitionCompiler;
use Jmf\CrudEngine\Compilation\Action\ReadActionDefinitionCompiler;
use Jmf\CrudEngine\Compilation\Action\UpdateActionDefinitionCompiler;
use Jmf\CrudEngine\Compilation\Resolution\ConfigurationValueResolver;
use Jmf\CrudEngine\Compilation\Resolution\MapResolver;
use Jmf\CrudEngine\Compilation\Resolution\OverridableConfigurationValueResolver;
use Jmf\CrudEngine\Compilation\Resolution\PatternsResolver;
use Jmf\CrudEngine\Compilation\Resolution\SchemaValueExpander;
use Jmf\TemplateRendering\TemplateRenderer;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

/**
 * Builds a {@see Compiler} (and its per-concern part compilers) with a
 * self-contained placeholder expander (a minimal Twig environment), for use at container
 * build time where no DI services exist.
 */
final readonly class CompilerFactory
{
    public function create(): Compiler
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

        $sharedDependencies = [
            $configurationValueResolver,
            $mapResolver,
            $patternsResolver,
            $formDefinitionCompiler,
            $routeDefinitionCompiler,
            $redirectionDefinitionCompiler,
            $viewDefinitionCompiler,
        ];

        return new Compiler(
            $mapResolver,
            [
                new CreateActionDefinitionCompiler(...$sharedDependencies),
                new DeleteActionDefinitionCompiler(...$sharedDependencies),
                new IndexActionDefinitionCompiler(...$sharedDependencies),
                new ReadActionDefinitionCompiler(...$sharedDependencies),
                new UpdateActionDefinitionCompiler(...$sharedDependencies),
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
        // strict_variables makes an unknown placeholder (a typo, or a key referencing another
        // key) fail at container build time instead of silently rendering to an empty string
        // (which would surface much later as a broken route/view path).
        $twigEnvironment = new Environment(
            new ArrayLoader(),
            ['strict_variables' => true],
        );
        $twigEnvironment->addExtension(new StringExtension());

        return $twigEnvironment;
    }
}
