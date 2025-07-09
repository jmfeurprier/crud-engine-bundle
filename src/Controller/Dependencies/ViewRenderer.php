<?php

namespace Jmf\CrudEngine\Controller\Dependencies;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineViewRenderingException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

readonly class ViewRenderer
{
    public function __construct(
        private TemplateRendererInterface $templateRenderer,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @throws CrudEngineViewRenderingException
     */
    public function render(
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
            throw new CrudEngineViewRenderingException(
                actionConfiguration: $actionConfiguration,
                previousException:   $e,
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
}
