<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Redirection;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineRedirectionException;
use Jmf\CrudEngine\Exception\CrudEngineRedirectionParameterRenderingException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

readonly class RedirectionGenerator
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private TemplateRendererInterface $templateRenderer,
    ) {
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     * @throws CrudEngineRedirectionException
     * @throws CrudEngineRedirectionParameterRenderingException
     */
    public function generate(
        ActionDefinition $actionDefinition,
        object $entity,
    ): RedirectResponse {
        $route      = $this->getRedirectRoute($actionDefinition);
        $parameters = $this->getRedirectRouteParameters($actionDefinition, $entity);

        try {
            $url = $this->urlGenerator->generate($route, $parameters);
        } catch (Throwable $e) {
            throw new CrudEngineRedirectionException($actionDefinition, $e);
        }

        $fragment = $this->getRedirectFragment($actionDefinition);

        if (null !== $fragment) {
            $url .= "#{$fragment}";
        }

        return new RedirectResponse($url);
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    private function getRedirectRoute(ActionDefinition $actionDefinition): string
    {
        return $actionDefinition->getRedirectionDefinition()->getRoute();
    }

    /**
     * @return array<string, string>
     *
     * @throws CrudEngineMissingConfigurationException
     * @throws CrudEngineRedirectionParameterRenderingException
     */
    private function getRedirectRouteParameters(
        ActionDefinition $actionDefinition,
        object $entity,
    ): array {
        $parameterTemplates = $actionDefinition->getRedirectionDefinition()->getParameters();
        $parameters  = [];

        foreach ($parameterTemplates as $key => $parameterTemplate) {
            $parameters[$key] = $this->getRedirectRouteParameter(
                $actionDefinition,
                $key,
                $parameterTemplate,
                $entity,
            );
        }

        return $parameters;
    }

    /**
     * @throws CrudEngineRedirectionParameterRenderingException
     */
    private function getRedirectRouteParameter(
        ActionDefinition $actionDefinition,
        string $key,
        string $parameterTemplate,
        object $entity,
    ): string {
        try {
            return $this->templateRenderer->renderFromString(
                $parameterTemplate,
                [
                    '_entity' => $entity,
                ],
            );
        } catch (Throwable $e) {
            throw new CrudEngineRedirectionParameterRenderingException(
                actionDefinition: $actionDefinition,
                key:                 $key,
                definition:          $parameterTemplate,
                previousException:   $e,
            );
        }
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    private function getRedirectFragment(
        ActionDefinition $actionDefinition,
    ): ?string {
        return $actionDefinition->getRedirectionDefinition()->getFragment();
    }
}
