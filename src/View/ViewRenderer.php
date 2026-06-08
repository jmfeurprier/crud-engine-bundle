<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\View;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Definition\FallbackMode;
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
        ActionDefinition $actionDefinition,
        array $viewVariables,
        array $defaults,
    ): Response {
        $parameters = $this->getViewParameters(
            $viewVariables,
            $defaults,
            $actionDefinition,
        );

        $configuredPath = $actionDefinition->getViewDefinition()->getPath();

        if ($this->twigEnvironment->getLoader()->exists($configuredPath)) {
            $viewPath = $configuredPath;
        } else {
            if (FallbackMode::FAIL === $actionDefinition->getViewDefinition()->getFallbackMode()) {
                throw new CrudEngineMissingViewException($actionDefinition, $configuredPath);
            }

            $viewPath = sprintf(
                self::BUILT_IN_TEMPLATE,
                $actionDefinition->getEntityAction()->getAction()->value,
            );

            $parameters['_crud_engine'] = $this->getProvidedContext(
                $actionDefinition,
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
                actionDefinition: $actionDefinition,
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
        ActionDefinition $actionDefinition,
        string $viewPath,
    ): array {
        return [
            'entityClass' => $actionDefinition->getEntityAction()->getEntityClass(),
            'action'      => $actionDefinition->getEntityAction()->getAction()->value,
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
        ActionDefinition $actionDefinition,
    ): array {
        $parameters = array_merge($viewVariables, $defaults);
        $configVars = $actionDefinition->getViewDefinition()->getVariables();

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
