<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Form\ActionFormConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Form\FormFallbackMode;
use Jmf\CrudEngine\Configuration\Entities\Action\Redirection\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\ActionRouteConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ViewFallbackMode;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Override;
use Webmozart\Assert\Assert;

/**
 * @phpstan-import-type ResolvedConfigurations from ActionConfigurationResolver
 * @phpstan-import-type ResolvedAction from ActionConfigurationResolver
 */
class ActionConfigurationRepository implements ActionConfigurationRepositoryInterface
{
    /**
     * @var array<class-string, array<non-empty-string, ActionConfiguration>>|null
     */
    private ?array $hydrated = null;

    /**
     * @param ResolvedConfigurations $resolvedConfigurations
     */
    public function __construct(
        private readonly array $resolvedConfigurations,
    ) {
    }

    #[Override]
    public function get(
        string $entityClass,
        string $action,
    ): ActionConfiguration {
        return $this->tryGet($entityClass, $action)
            ??
            throw new CrudEngineMissingConfigurationException(
                $entityClass,
                $action,
            );
    }

    #[Override]
    public function tryGet(
        string $entityClass,
        string $action,
    ): ?ActionConfiguration {
        return $this->hydrate()[$entityClass][$action] ?? null;
    }

    #[Override]
    public function all(): iterable
    {
        foreach ($this->hydrate() as $configurationsByAction) {
            yield from $configurationsByAction;
        }
    }

    /**
     * @return array<class-string, array<non-empty-string, ActionConfiguration>>
     */
    private function hydrate(): array
    {
        if (null !== $this->hydrated) {
            return $this->hydrated;
        }

        $hydrated = [];

        foreach ($this->resolvedConfigurations as $entityClass => $actions) {
            foreach ($actions as $action => $resolvedAction) {
                $hydrated[$entityClass][$action] = $this->hydrateAction(
                    $entityClass,
                    $action,
                    $resolvedAction,
                );
            }
        }

        return $this->hydrated = $hydrated;
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
        $route       = $resolvedAction['route'];
        $redirection = $resolvedAction['redirection'];
        $view        = $resolvedAction['view'];

        $viewFallbackMode = ViewFallbackMode::tryFrom($view['fallback']);

        Assert::notNull(
            $viewFallbackMode,
            sprintf('Unknown view fallback mode "%s".', $view['fallback']),
        );

        $formFallbackMode = FormFallbackMode::tryFrom($resolvedAction['formFallback']);

        Assert::notNull(
            $formFallbackMode,
            sprintf('Unknown form fallback mode "%s".', $resolvedAction['formFallback']),
        );

        return new ActionConfiguration(
            entityClass:              $entityClass,
            action:                   $action,
            helperClass:              $resolvedAction['helperClass'],
            formConfiguration:        new ActionFormConfiguration(
                                          formTypeClass:           $resolvedAction['formTypeClass'],
                                          suggestedFormTypeClass:  $resolvedAction['formSuggestedClass'],
                                          formFallbackMode:        $formFallbackMode,
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
