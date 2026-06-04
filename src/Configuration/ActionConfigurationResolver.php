<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Configuration\Resolution\ConfigurationValueResolver;
use Jmf\CrudEngine\Configuration\Resolution\FormConfigurationResolver;
use Jmf\CrudEngine\Configuration\Resolution\MapResolver;
use Jmf\CrudEngine\Configuration\Resolution\PatternsResolver;
use Jmf\CrudEngine\Configuration\Resolution\RedirectionConfigurationResolver;
use Jmf\CrudEngine\Configuration\Resolution\RouteConfigurationResolver;
use Jmf\CrudEngine\Configuration\Resolution\ViewConfigurationResolver;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Webmozart\Assert\Assert;

/**
 * Resolves the bundle configuration array into a normalized, fully-expanded array, once, at
 * container build time. The entities/actions traversal and the keys/helper resolution live
 * here; the form/route/redirection/view parts are delegated to dedicated resolvers.
 * Placeholders that depend only on the entity class and the action are expanded here;
 * request-time placeholders (e.g. redirection parameters referencing the entity) are kept
 * verbatim.
 *
 * @phpstan-import-type ResolvedForm from FormConfigurationResolver
 * @phpstan-import-type ResolvedRoute from RouteConfigurationResolver
 * @phpstan-import-type ResolvedRedirection from RedirectionConfigurationResolver
 * @phpstan-import-type ResolvedView from ViewConfigurationResolver
 *
 * @phpstan-type ResolvedAction array{form: ResolvedForm, helperClass: class-string|null, route: ResolvedRoute, redirection: ResolvedRedirection|null, view: ResolvedView}
 * @phpstan-type ResolvedConfigurations array<class-string, array<non-empty-string, ResolvedAction>>
 */
readonly class ActionConfigurationResolver
{
    /**
     * @var array<non-empty-string, non-empty-string>
     */
    private const array DEFAULT_KEYS = [
        'ActionKey'      => "{{ action|u.camel.title }}",
        'ActionKeys'     => "{{ action|u.camel.title|plural }}",
        'actionKey'      => "{{ action|u.camel }}",
        'actionKeys'     => "{{ action|u.camel|plural }}",
        'action_key'     => "{{ action|u.snake }}",
        'action_keys'    => "{{ action|u.snake|plural }}",
        'actiondashkey'  => "{{ action|u.kebab }}",
        'actiondashkeys' => "{{ action|u.kebab|plural }}",
        'EntityKey'      => "{{ entityClass|u.afterLast('\\\\').camel.title }}",
        'EntityKeys'     => "{{ entityClass|u.afterLast('\\\\').camel.title|plural }}",
        'entityKey'      => "{{ entityClass|u.afterLast('\\\\').camel }}",
        'entityKeys'     => "{{ entityClass|u.afterLast('\\\\').camel|plural }}",
        'entity_key'     => "{{ entityClass|u.afterLast('\\\\').snake }}",
        'entity_keys'    => "{{ entityClass|u.afterLast('\\\\').snake|plural }}",
        'entitydashkey'  => "{{ entityClass|u.afterLast('\\\\').kebab }}",
        'entitydashkeys' => "{{ entityClass|u.afterLast('\\\\').kebab|plural }}",
    ];

    /**
     * @var list<non-empty-string>
     */
    private const array DEFAULT_HELPERS = [
        "App\\Controller\\{{ EntityKey }}\\{{ ActionKey }}ActionHelper",
        "App\\Controller\\{{ EntityKey }}{{ ActionKey }}ActionHelper",
    ];

    public function __construct(
        private ConfigurationValueResolver $configurationValueResolver,
        private MapResolver $mapResolver,
        private PatternsResolver $patternsResolver,
        private FormConfigurationResolver $formConfigurationResolver,
        private RouteConfigurationResolver $routeConfigurationResolver,
        private RedirectionConfigurationResolver $redirectionConfigurationResolver,
        private ViewConfigurationResolver $viewConfigurationResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return ResolvedConfigurations
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    public function resolve(array $config): array
    {
        $schema = $this->mapResolver->resolve($config, 'schema');

        Assert::keyExists($config, 'entities');
        $entitiesConfig = $config['entities'];
        Assert::isMap($entitiesConfig);

        $resolved = [];

        foreach ($entitiesConfig as $entityClass => $entityConfig) {
            Assert::classExists($entityClass);
            Assert::isMap($entityConfig);

            if (!array_key_exists('actions', $entityConfig)) {
                throw new CrudEngineMissingConfigurationException(
                    entityClass:      $entityClass,
                    configurationKey: 'actions',
                );
            }

            $actionsConfig = $entityConfig['actions'];
            Assert::isMap($actionsConfig);

            foreach ($actionsConfig as $action => $actionConfig) {
                Assert::stringNotEmpty($action);
                Assert::isMap($actionConfig);

                $resolved[$entityClass][$action] = $this->resolveAction(
                    $schema,
                    $entityClass,
                    $action,
                    $actionConfig,
                );
            }
        }

        return $resolved;
    }

    /**
     * @param array<string, mixed> $schema
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @return ResolvedAction
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    private function resolveAction(
        array $schema,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): array {
        $keys = $this->resolveKeys($schema, $entityClass, $action);

        return [
            'form'        => $this->formConfigurationResolver->resolve(
                $schema,
                $keys,
                $entityClass,
                $action,
                $actionConfig,
            ),
            'helperClass' => $this->resolveHelperClass($schema, $keys, $entityClass, $action, $actionConfig),
            'route'       => $this->routeConfigurationResolver->resolve(
                $schema,
                $keys,
                $entityClass,
                $action,
                $actionConfig,
            ),
            'redirection' => $this->redirectionConfigurationResolver->resolve(
                $schema,
                $keys,
                $entityClass,
                $action,
                $actionConfig,
            ),
            'view'        => $this->viewConfigurationResolver->resolve(
                $schema,
                $keys,
                $entityClass,
                $action,
                $actionConfig,
            ),
        ];
    }

    /**
     * @param array<string, mixed> $schema
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     *
     * @return array<non-empty-string, non-empty-string>
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function resolveKeys(
        array $schema,
        string $entityClass,
        string $action,
    ): array {
        /** @var array<non-empty-string, non-empty-string> $patterns */
        $patterns = array_merge(
            self::DEFAULT_KEYS,
            $this->mapResolver->resolve($schema, 'keys'),
        );

        $keys = [];

        foreach ($patterns as $name => $pattern) {
            $value = $this->configurationValueResolver->resolve(
                value:       $pattern,
                keys:        [],
                entityClass: $entityClass,
                action:      $action,
            );

            Assert::stringNotEmpty($value);

            $keys[$name] = $value;
        }

        return $keys;
    }

    /**
     * @param array<string, mixed>                      $schema
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $actionConfig
     *
     * @return class-string|null
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function resolveHelperClass(
        array $schema,
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?string {
        if (array_key_exists('helper', $actionConfig)) {
            $helperClass = $actionConfig['helper'];

            if (null === $helperClass) {
                return null;
            }

            Assert::string($helperClass);
            Assert::classExists($helperClass);

            return $helperClass;
        }

        foreach ($this->patternsResolver->resolve($schema, 'helper', self::DEFAULT_HELPERS) as $pattern) {
            $class = $this->configurationValueResolver->resolve($pattern, $keys, $entityClass, $action);

            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }
}
