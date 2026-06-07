<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Registry;

use Jmf\CrudEngine\Resolution\ActionConfigurationResolver;
use Jmf\CrudEngine\Resolution\Action\ActionConfigResolverInterface;
use Jmf\CrudEngine\Form\FormFallbackMode;
use Jmf\CrudEngine\Model\ActionConfiguration;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\View\ViewFallbackMode;
use Webmozart\Assert\Assert;

/**
 * Maps the normalized, resolved configuration array into the immutable
 * {@see ActionConfiguration} DTO graph.
 *
 * @phpstan-import-type ResolvedConfigurations from ActionConfigurationResolver
 * @phpstan-import-type ResolvedAction from ActionConfigResolverInterface
 */
readonly class ActionConfigurationHydrator
{
    /**
     * @param ResolvedConfigurations $resolvedConfigurations
     *
     * @return array<class-string, array<non-empty-string, ActionConfiguration>>
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
     * @param ResolvedAction   $resolvedAction
     */
    private function hydrateAction(
        string $entityClass,
        string $action,
        array $resolvedAction,
    ): ActionConfiguration {
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

            $formConfiguration = new ActionFormConfiguration(
                formTypeClass:          $form['typeClass'],
                suggestedFormTypeClass: $form['suggestedClass'],
                formFallbackMode:       $formFallbackMode,
            );
        }

        return new ActionConfiguration(
            entityAction:             new EntityAction(
                                          $entityClass,
                                          $action,
                                      ),
            helperClass:              $resolvedAction['helperClass'],
            formConfiguration:        $formConfiguration,
            redirectionConfiguration: null === $redirection
                                          ? null
                                          : new ActionRedirectionConfiguration(
                                              route:      $redirection['route'],
                                              parameters: $redirection['parameters'],
                                              fragment:   $redirection['fragment'],
                                          ),
            routeConfiguration:       new ActionRouteConfiguration(
                                          name:         $route['name'],
                                          path:         $route['path'],
                                          requirements: $route['requirements'],
                                      ),
            viewConfiguration:        new ActionViewConfiguration(
                                          path:             $view['path'],
                                          variables:        $view['variables'],
                                          viewFallbackMode: $viewFallbackMode,
                                      ),
        );
    }
}
