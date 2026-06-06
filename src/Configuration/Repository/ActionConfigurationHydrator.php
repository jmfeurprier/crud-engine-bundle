<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Repository;

use Jmf\CrudEngine\Configuration\ActionConfigurationResolver;
use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Form\ActionFormConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Form\FormFallbackMode;
use Jmf\CrudEngine\Configuration\Entities\Action\Redirection\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\ActionRouteConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ViewFallbackMode;
use Jmf\CrudEngine\Model\EntityAction;
use Webmozart\Assert\Assert;

/**
 * Maps the normalized, resolved configuration array into the immutable
 * {@see ActionConfiguration} DTO graph.
 *
 * @phpstan-import-type ResolvedConfigurations from ActionConfigurationResolver
 * @phpstan-import-type ResolvedAction from ActionConfigurationResolver
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

        $formFallbackMode = FormFallbackMode::tryFrom($form['fallback']);

        Assert::notNull(
            $formFallbackMode,
            sprintf('Unknown form fallback mode "%s".', $form['fallback']),
        );

        return new ActionConfiguration(
            entityAction:             new EntityAction(
                                          $entityClass,
                                          $action,
                                      ),
            helperClass:              $resolvedAction['helperClass'],
            formConfiguration:        new ActionFormConfiguration(
                                          formTypeClass:          $form['typeClass'],
                                          suggestedFormTypeClass: $form['suggestedClass'],
                                          formFallbackMode:       $formFallbackMode,
                                      ),
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
