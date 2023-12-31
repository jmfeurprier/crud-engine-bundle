<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

trait WithRedirectionTrait
{
    private UrlGeneratorInterface $urlGenerator;

    private Environment $twigEnvironment;

    /**
     * @throws CrudEngineMissingConfigurationException
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function redirectOnSuccess(
        ActionConfiguration $actionConfiguration,
        object $entity,
    ): Response {
        return new RedirectResponse(
            $this->urlGenerator->generate(
                $this->getRedirectRoute($actionConfiguration),
                $this->getRedirectRouteParameters($actionConfiguration, $entity)
            )
        );
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
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function getRedirectRouteParameters(
        ActionConfiguration $actionConfiguration,
        object $entity,
    ): array {
        $definitions = $actionConfiguration->getRedirectionConfiguration()->getParameters()->all();
        $parameters  = [];

        foreach ($definitions as $key => $definition) {
            $template = $this->twigEnvironment->createTemplate($definition);

            $parameters[$key] = $template->render(
                [
                    '_entity' => $entity,
                ]
            );
        }

        return $parameters;
    }
}
