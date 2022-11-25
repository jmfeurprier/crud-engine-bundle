<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;

trait WithViewTrait
{
    private TwigEnvironment $twigEnvironment;

    protected function render(array $defaults): Response
    {
        return new Response(
            $this->twigEnvironment->render(
                $this->getViewPath(),
                $this->getViewContext($defaults)
            )
        );
    }

    protected function getViewPath(): string
    {
        return $this->actionProperties['view']['path'];
    }

    abstract protected function getViewContext(array $defaults): array;

    protected function mapViewVariables(
        array $actionProperties,
        array $defaults
    ): array {
        $variables = [];

        foreach ($defaults as $variable => $value) {
            $variableName             = $actionProperties['view']['variables'][$variable] ?? $variable;
            $variables[$variableName] = $value;
        }

        return $variables;
    }
}
