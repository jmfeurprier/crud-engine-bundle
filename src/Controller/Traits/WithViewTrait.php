<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

trait WithViewTrait
{
    private TwigEnvironment $twigEnvironment;

    /**
     * @param array<string, mixed> $defaults
     *
     * @throws CrudEngineMissingConfigurationException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function render(
        ActionConfiguration $actionConfiguration,
        array $defaults,
    ): Response {
        return new Response(
            $this->twigEnvironment->render(
                $this->getViewPath($actionConfiguration),
                $this->getViewContext($actionConfiguration, $defaults)
            )
        );
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    private function getViewPath(ActionConfiguration $actionConfiguration): string
    {
        return $actionConfiguration->getViewConfiguration()->getPath();
    }

    /**
     * @param array<string, mixed> $defaults
     *
     * @return array<string, mixed>
     */
    abstract private function getViewContext(
        ActionConfiguration $actionConfiguration,
        array $defaults,
    ): array;

    /**
     * @param array<string, mixed> $defaults
     *
     * @return array<string, mixed>
     *
     * @throws CrudEngineMissingConfigurationException
     */
    private function mapViewVariables(
        ActionConfiguration $actionConfiguration,
        array $defaults,
    ): array {
        $variables = [];

        foreach ($defaults as $variable => $value) {
            $variableName = $actionConfiguration->getViewConfiguration()->getVariables()->tryGet(
                $variable,
                $variable,
            );

            $variables[$variableName] = $value;
        }

        return $variables;
    }
}
