<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
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
     * @param array<string,mixed> $actionProperties
     */
    private function redirectOnSuccess(
        array $actionProperties,
        object $entity
    ): Response {
        return new RedirectResponse(
            $this->urlGenerator->generate(
                $this->getRedirectRoute($actionProperties),
                $this->getRedirectRouteParameters($actionProperties, $entity)
            )
        );
    }

    /**
     * @param array<string,mixed> $actionProperties
     */
    private function getRedirectRoute(array $actionProperties): string
    {
        return $actionProperties['redirection']['route'];
    }

    /**
     * @param array<string,mixed> $actionProperties
     *
     * @return array<string, mixed>
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function getRedirectRouteParameters(
        array $actionProperties,
        object $entity
    ): array {
        $definitions = $actionProperties['redirection']['parameters'] ?? [];
        $parameters  = [];

        foreach ($definitions as $key => $definition) {
            if (!is_string($key)) {
                throw new CrudEngineInvalidConfigurationException();
            }

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
