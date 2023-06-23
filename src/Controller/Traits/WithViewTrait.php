<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

trait WithViewTrait
{
    private TwigEnvironment $twigEnvironment;

    /**
     * @param array<string, mixed> $actionProperties
     * @param array<string, mixed> $defaults
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function render(
        array $actionProperties,
        array $defaults
    ): Response {
        return new Response(
            $this->twigEnvironment->render(
                $this->getViewPath($actionProperties),
                $this->getViewContext($actionProperties, $defaults)
            )
        );
    }

    /**
     * @param array<string, mixed> $actionProperties
     */
    protected function getViewPath(array $actionProperties): string
    {
        return $actionProperties['view']['path'];
    }

    /**
     * @param array<string, mixed> $actionProperties
     * @param array<string, mixed> $defaults
     *
     * @return array<string, mixed>
     */
    abstract protected function getViewContext(
        array $actionProperties,
        array $defaults
    ): array;

    /**
     * @param array<string, mixed> $actionProperties
     * @param array<string, mixed> $defaults
     *
     * @return array<string, mixed>
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    protected function mapViewVariables(
        array $actionProperties,
        array $defaults
    ): array {
        $variables = [];

        foreach ($defaults as $variable => $value) {
            $variableName = $actionProperties['view']['variables'][$variable] ?? $variable;

            if (!is_string($variableName)) {
                throw new CrudEngineInvalidConfigurationException();
            }

            $variables[$variableName] = $value;
        }

        return $variables;
    }
}
