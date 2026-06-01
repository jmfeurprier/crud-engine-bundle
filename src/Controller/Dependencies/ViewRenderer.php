<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller\Dependencies;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ViewFallbackMode;
use Jmf\CrudEngine\Exception\CrudEngineMissingViewException;
use Jmf\CrudEngine\Exception\CrudEngineViewRenderingException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Twig\Environment as TwigEnvironment;
use Webmozart\Assert\Assert;

readonly class ViewRenderer
{
    /**
     * @const non-empty-string
     */
    private const string BUILT_IN_TEMPLATE = '@JmfCrudEngine/%s.html.twig';

    public function __construct(
        private TemplateRendererInterface $templateRenderer,
        private TwigEnvironment $twigEnvironment,
    ) {
    }

    /**
     * @param array<string, mixed> $viewVariables
     * @param array<string, mixed> $defaults
     *
     * @throws CrudEngineMissingViewException
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

        $viewPath = $this->getViewPath($actionConfiguration);

        try {
            return new Response(
                $this->templateRenderer->renderFromFile(
                    $viewPath,
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

    /**
     * @throws CrudEngineMissingViewException
     */
    private function getViewPath(ActionConfiguration $actionConfiguration): string
    {
        $path = $actionConfiguration->getViewConfiguration()->getPath();

        if ($this->twigEnvironment->getLoader()->exists($path)) {
            return $path;
        }

        if (ViewFallbackMode::FAIL === $actionConfiguration->getViewConfiguration()->getViewFallbackMode()) {
            throw new CrudEngineMissingViewException($actionConfiguration, $path);
        }

        return sprintf(self::BUILT_IN_TEMPLATE, $actionConfiguration->getAction());
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

            $variables = $configVars[$key] ?? [];

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
