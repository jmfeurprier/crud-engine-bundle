<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration;

use Jmf\TemplateRendering\TemplateRenderer;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

/**
 * Builds an {@see ActionConfigurationResolver} with a self-contained placeholder expander
 * (a minimal Twig environment), for use at container build time where no DI services exist.
 */
final readonly class ActionConfigurationResolverFactory
{
    public function create(): ActionConfigurationResolver
    {
        return new ActionConfigurationResolver(
            $this->getSchemaValueExpander(),
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
