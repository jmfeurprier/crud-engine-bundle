<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineRenderingException;
use Jmf\TemplateRendering\Exception\TemplateRenderingException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

trait WithViewTrait
{
    private TemplateRendererInterface $templateRenderer;

    /**
     * @param array<string, mixed> $context
     *
     * @throws CrudEngineMissingConfigurationException
     * @throws TemplateRenderingException
     */
    private function render(
        ActionConfiguration $actionConfiguration,
        array $context,
    ): Response {
        try {
            return new Response(
                $this->templateRenderer->renderFromFile(
                    $this->getViewPath($actionConfiguration),
                    $context,
                ),
            );
        } catch (Throwable $e) {
            throw new CrudEngineRenderingException(

                previousException: $e,
            );
        }
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
