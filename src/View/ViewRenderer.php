<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\View;

use Jmf\CrudEngine\Exception\CrudEngineMissingViewException;
use Jmf\CrudEngine\Exception\CrudEngineViewRenderingException;
use Jmf\CrudEngine\Model\ActionConfiguration;
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

        $configuredPath = $actionConfiguration->getViewConfiguration()->getPath();

        if ($this->twigEnvironment->getLoader()->exists($configuredPath)) {
            $viewPath = $configuredPath;
        } else {
            if (ViewFallbackMode::FAIL === $actionConfiguration->getViewConfiguration()->getViewFallbackMode()) {
                throw new CrudEngineMissingViewException($actionConfiguration, $configuredPath);
            }

            $viewPath = sprintf(
                self::BUILT_IN_TEMPLATE,
                $actionConfiguration->getEntityAction()->getAction(),
            );

            $parameters['_crud_engine'] = $this->getProvidedContext(
                $actionConfiguration,
                $configuredPath,
            );
        }

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
     * Context handed to a built-in (provided) template so it can suggest how to customize.
     *
     * @return array<string, mixed>
     */
    private function getProvidedContext(
        ActionConfiguration $actionConfiguration,
        string $viewPath,
    ): array {
        return [
            'entityClass' => $actionConfiguration->getEntityAction()->getEntityClass(),
            'action'      => $actionConfiguration->getEntityAction()->getAction(),
            'viewPath'    => $viewPath,
        ];
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
