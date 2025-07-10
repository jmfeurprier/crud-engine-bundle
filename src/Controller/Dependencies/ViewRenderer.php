<?php

namespace Jmf\CrudEngine\Controller\Dependencies;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineViewRenderingException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Webmozart\Assert\Assert;

readonly class ViewRenderer
{
    public function __construct(
        private TemplateRendererInterface $templateRenderer,
    ) {
    }

    /**
     * @param array<string, mixed> $viewVariables
     * @param array<string, mixed> $defaults
     *
     * @throws CrudEngineViewRenderingException
     */
    public function render(
        ActionConfiguration $actionConfiguration,
        array $viewVariables,
        array $defaults,
    ): Response {
        $parameters = $this->getViewParameters(
            $viewVariables,
            $defaults,
            $actionConfiguration,
        );

        try {
            return new Response(
                $this->templateRenderer->renderFromFile(
                    $this->getViewPath($actionConfiguration),
                    $parameters,
                ),
            );
        } catch (Throwable $e) {
            throw new CrudEngineViewRenderingException(
                actionConfiguration: $actionConfiguration,
                previousException:   $e,
            );
        }
    }

    private function getViewPath(ActionConfiguration $actionConfiguration): string
    {
        return $actionConfiguration->getViewConfiguration()->getPath();
    }

    /**
     * @param array<string, mixed> $viewVariables
     * @param array<string, mixed> $defaults
     *
     * @return array<string, mixed>
     */
    private function getViewParameters(
        array $viewVariables,
        array $defaults,
        ActionConfiguration $actionConfiguration,
    ): array {
        $parameters = array_merge($viewVariables, $defaults);
        $configVars = $actionConfiguration->getViewConfiguration()->getVariables();

        foreach ($parameters as $key => $value) {
            Assert::stringNotEmpty($key);

            $variables = $configVars->tryGet($key);

            if ([] === $variables) {
                continue;
            }

            unset($parameters[$key]);

            foreach ($variables as $variable) {
                $parameters[$variable] = $value;
            }
        }

        return $parameters;
    }
}
