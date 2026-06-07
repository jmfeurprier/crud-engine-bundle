<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Registry;

use Jmf\CrudEngine\Compilation\ActionDefinitionCompiler;
use Jmf\CrudEngine\Compilation\Action\ActionDefinitionCompilerInterface;
use Jmf\CrudEngine\Form\FormFallbackMode;
use Jmf\CrudEngine\Model\ActionDefinition;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\View\ViewFallbackMode;
use Webmozart\Assert\Assert;

/**
 * Maps the normalized, resolved configuration array into the immutable
 * {@see ActionDefinition} DTO graph.
 *
 * @phpstan-import-type CompiledDefinitions from ActionDefinitionCompiler
 * @phpstan-import-type CompiledAction from ActionDefinitionCompilerInterface
 */
readonly class ActionDefinitionHydrator
{
    /**
     * @param CompiledDefinitions $resolvedConfigurations
     *
     * @return array<class-string, array<non-empty-string, ActionDefinition>>
     */
    public function hydrate(array $resolvedConfigurations): array
    {
        $hydrated = [];

        foreach ($resolvedConfigurations as $entityClass => $actions) {
            foreach ($actions as $action => $resolvedAction) {
                $hydrated[$entityClass][$action] = $this->hydrateAction(
                    $entityClass,
                    $action,
                    $resolvedAction,
                );
            }
        }

        return $hydrated;
    }

    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     * @param CompiledAction   $resolvedAction
     */
    private function hydrateAction(
        string $entityClass,
        string $action,
        array $resolvedAction,
    ): ActionDefinition {
        $form        = $resolvedAction['form'];
        $route       = $resolvedAction['route'];
        $redirection = $resolvedAction['redirection'];
        $view        = $resolvedAction['view'];

        $viewFallbackMode = ViewFallbackMode::tryFrom($view['fallback']);

        Assert::notNull(
            $viewFallbackMode,
            sprintf('Unknown view fallback mode "%s".', $view['fallback']),
        );

        $formConfiguration = null;

        if (null !== $form) {
            $formFallbackMode = FormFallbackMode::tryFrom($form['fallback']);

            Assert::notNull(
                $formFallbackMode,
                sprintf('Unknown form fallback mode "%s".', $form['fallback']),
            );

            $formConfiguration = new FormDefinition(
                formTypeClass:          $form['typeClass'],
                suggestedFormTypeClass: $form['suggestedClass'],
                formFallbackMode:       $formFallbackMode,
            );
        }

        return new ActionDefinition(
            entityAction:             new EntityAction(
                                          $entityClass,
                                          $action,
                                      ),
            helperClass:              $resolvedAction['helperClass'],
            formConfiguration:        $formConfiguration,
            redirectionConfiguration: null === $redirection
                                          ? null
                                          : new RedirectionDefinition(
                                              route:      $redirection['route'],
                                              parameters: $redirection['parameters'],
                                              fragment:   $redirection['fragment'],
                                          ),
            routeConfiguration:       new RouteDefinition(
                                          name:         $route['name'],
                                          path:         $route['path'],
                                          requirements: $route['requirements'],
                                      ),
            viewConfiguration:        new ViewDefinition(
                                          path:             $view['path'],
                                          variables:        $view['variables'],
                                          viewFallbackMode: $viewFallbackMode,
                                      ),
        );
    }
}
