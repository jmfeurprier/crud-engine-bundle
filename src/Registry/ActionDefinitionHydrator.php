<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Registry;

use Jmf\CrudEngine\Compilation\Action\ActionDefinitionCompilerInterface;
use Jmf\CrudEngine\Compilation\Compiler;
use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Definition\FallbackMode;
use Jmf\CrudEngine\Definition\FormDefinition;
use Jmf\CrudEngine\Definition\RedirectionDefinition;
use Jmf\CrudEngine\Definition\RouteDefinition;
use Jmf\CrudEngine\Definition\ViewDefinition;
use Jmf\CrudEngine\Model\EntityAction;
use Webmozart\Assert\Assert;

/**
 * Maps the normalized, resolved configuration array into the immutable
 * {@see ActionDefinition} DTO graph.
 *
 * @phpstan-import-type CompiledDefinitions from Compiler
 * @phpstan-import-type CompiledAction from ActionDefinitionCompilerInterface
 */
readonly class ActionDefinitionHydrator
{
    /**
     * @param CompiledDefinitions $compiledDefinitions
     *
     * @return array<class-string, array<non-empty-string, ActionDefinition>>
     */
    public function hydrate(array $compiledDefinitions): array
    {
        $hydrated = [];

        foreach ($compiledDefinitions as $entityClass => $actions) {
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

        $viewFallbackMode = FallbackMode::tryFrom($view['fallback']);

        Assert::notNull(
            $viewFallbackMode,
            sprintf('Unknown view fallback mode "%s".', $view['fallback']),
        );

        $formDefinition = null;

        if (null !== $form) {
            $formFallbackMode = FallbackMode::tryFrom($form['fallback']);

            Assert::notNull(
                $formFallbackMode,
                sprintf('Unknown form fallback mode "%s".', $form['fallback']),
            );

            $formDefinition = new FormDefinition(
                formTypeClass:          $form['typeClass'],
                suggestedFormTypeClass: $form['suggestedClass'],
                fallbackMode:       $formFallbackMode,
            );
        }

        return new ActionDefinition(
            entityAction:             new EntityAction(
                                          $entityClass,
                                          $action,
                                      ),
            helperClass:              $resolvedAction['helperClass'],
            formDefinition:        $formDefinition,
            redirectionDefinition: null === $redirection
                                          ? null
                                          : new RedirectionDefinition(
                                              route:      $redirection['route'],
                                              parameters: $redirection['parameters'],
                                              fragment:   $redirection['fragment'],
                                          ),
            routeDefinition:       new RouteDefinition(
                                          name:         $route['name'],
                                          path:         $route['path'],
                                          requirements: $route['requirements'],
                                      ),
            viewDefinition:        new ViewDefinition(
                                          path:             $view['path'],
                                          variables:        $view['variables'],
                                          fallbackMode: $viewFallbackMode,
                                      ),
        );
    }
}
