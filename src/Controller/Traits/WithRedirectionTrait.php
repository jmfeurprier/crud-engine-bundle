<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

trait WithRedirectionTrait
{
    private UrlGeneratorInterface $urlGenerator;

    private Environment $twigEnvironment;

    private function redirectOnSuccess(array $actionProperties, object $entity): Response
    {
        return new RedirectResponse(
            $this->urlGenerator->generate(
                $this->getRedirectRoute($actionProperties),
                $this->getRedirectRouteParameters($actionProperties, $entity)
            )
        );
    }

    private function getRedirectRoute(array $actionProperties): string
    {
        return $actionProperties['redirection']['route'];
    }

    private function getRedirectRouteParameters(
        array $actionProperties,
        object $entity
    ): array {
        $definitions = $actionProperties['redirection']['parameters'] ?? [];
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
