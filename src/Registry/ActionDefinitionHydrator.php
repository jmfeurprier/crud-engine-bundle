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
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Model\CrudAction;
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
     * @return ActionDefinition[]
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function hydrate(array $compiledDefinitions): iterable
    {
        $hydrated = [];

        foreach ($compiledDefinitions as $entityClass => $actions) {
            foreach ($actions as $actionValue => $compiledAction) {
                $action = CrudAction::tryFrom($actionValue);

                Assert::isInstanceOf($action, CrudAction::class);

                $entityAction = new EntityAction(
                    $entityClass,
                    $action,
                );

                $hydrated[] = $this->hydrateEntityAction(
                    $entityAction,
                    $compiledAction,
                );
            }
        }

        return $hydrated;
    }

    /**
     * @param CompiledAction $compiledAction
     */
    private function hydrateEntityAction(
        EntityAction $entityAction,
        array $compiledAction,
    ): ActionDefinition {
        $form        = $compiledAction['form'];
        $route       = $compiledAction['route'];
        $redirection = $compiledAction['redirection'];
        $view        = $compiledAction['view'];

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
                fallbackMode:           $formFallbackMode,
            );
        }

        return new ActionDefinition(
            entityAction:          $entityAction,
            helperClass:           $compiledAction['helperClass'],
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
                                       path:         $view['path'],
                                       variables:    $view['variables'],
                                       fallbackMode: $viewFallbackMode,
                                   ),
        );
    }
}
