<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\TemplateRendering\Exception\TemplateRenderingException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

trait WithRedirectionTrait
{
    private UrlGeneratorInterface $urlGenerator;

    private TemplateRendererInterface $templateRenderer;

    /**
     * @throws CrudEngineMissingConfigurationException
     * @throws TemplateRenderingException
     */
    private function redirectOnSuccess(
        ActionConfiguration $actionConfiguration,
        object $entity,
    ): Response {
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
     * @throws TemplateRenderingException
     */
    private function getRedirectRouteParameters(
        ActionConfiguration $actionConfiguration,
        object $entity,
    ): array {
        $definitions = $actionConfiguration->getRedirectionConfiguration()->getParameters()->all();
        $parameters  = [];

        foreach ($definitions as $key => $definition) {
            $parameters[$key] = $this->templateRenderer->renderFromString(
                $definition,
                [
                    '_entity' => $entity,
                ],
            );
        }

        return $parameters;
    }

    private function getRedirectFragment(ActionConfiguration $actionConfiguration): ?string
    {
        return $actionConfiguration->getRedirectionConfiguration()->getFragment();
    }
}
