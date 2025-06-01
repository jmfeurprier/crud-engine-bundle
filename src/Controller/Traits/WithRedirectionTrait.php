<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineRedirectionParameterRenderingException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

trait WithRedirectionTrait
{
    private readonly UrlGeneratorInterface $urlGenerator;

    private readonly TemplateRendererInterface $templateRenderer;

    /**
     * @throws CrudEngineMissingConfigurationException
     * @throws CrudEngineRedirectionParameterRenderingException
     */
    private function redirectOnSuccess(
        ActionConfiguration $actionConfiguration,
        object $entity,
    ): RedirectResponse {
        $url = $this->urlGenerator->generate(
            $this->getRedirectRoute($actionConfiguration),
            $this->getRedirectRouteParameters($actionConfiguration, $entity),
        );

        $fragment = $this->getRedirectFragment($actionConfiguration);

        if (null !== $fragment) {
            $url .= "#{$fragment}";
        }

        return new RedirectResponse($url);
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    private function getRedirectRoute(ActionConfiguration $actionConfiguration): string
    {
        return $actionConfiguration->getRedirectionConfiguration()->getRoute();
    }

    /**
     * @return array<string, string>
     *
     * @throws CrudEngineMissingConfigurationException
     * @throws CrudEngineRedirectionParameterRenderingException
     */
    private function getRedirectRouteParameters(
        ActionConfiguration $actionConfiguration,
        object $entity,
    ): array {
        $definitions = $actionConfiguration->getRedirectionConfiguration()->getParameters()->all();
        $parameters  = [];

        foreach ($definitions as $key => $definition) {
            $parameters[$key] = $this->getRedirectRouteParameter(
                $actionConfiguration,
                $key,
                $definition,
                $entity,
            );
        }

        return $parameters;
    }

    /**
     * @throws CrudEngineRedirectionParameterRenderingException
     */
    private function getRedirectRouteParameter(
        ActionConfiguration $actionConfiguration,
        string $key,
        string $definition,
        object $entity,
    ): string {
        try {
            return $this->templateRenderer->renderFromString(
                $definition,
                [
                    '_entity' => $entity,
                ],
            );
        } catch (Throwable $e) {
            throw new CrudEngineRedirectionParameterRenderingException(
                actionConfiguration: $actionConfiguration,
                key:                 $key,
                definition:          $definition,
                previousException:   $e,
            );
        }
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    private function getRedirectFragment(
        ActionConfiguration $actionConfiguration,
    ): ?string {
        return $actionConfiguration->getRedirectionConfiguration()->getFragment();
    }
}
