<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Configuration\Entities\Action\Form\FormFallbackMode;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ViewFallbackMode;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Symfony\Component\Form\FormTypeInterface;
use Webmozart\Assert\Assert;

/**
 * Resolves the bundle configuration array into a normalized, fully-expanded array,
 * once, at container build time. Placeholders that depend only on the entity class and
 * the action are expanded here; request-time placeholders (e.g. redirection parameters
 * referencing the entity) are kept verbatim.
 *
 * @phpstan-type ResolvedRoute array{name: non-empty-string, path: non-empty-string, requirements: array<non-empty-string, non-empty-string>}
 * @phpstan-type ResolvedRedirection array{route: non-empty-string, parameters: array<string, string>, fragment: string|null}
 * @phpstan-type ResolvedView array{path: non-empty-string, variables: array<non-empty-string, list<non-empty-string>>, fallback: non-empty-string}
 * @phpstan-type ResolvedAction array{formTypeClass: class-string<FormTypeInterface>|null, formFallback: non-empty-string, helperClass: class-string|null, route: ResolvedRoute, redirection: ResolvedRedirection|null, view: ResolvedView}
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
     * @var non-empty-string
     */
    private const string DEFAULT_ROUTE_NAME = "{{ entity_key }}.{{ action_key }}";

    /**
     * @var array<non-empty-string, non-empty-string>
     */
    private const array DEFAULT_ROUTE_PATHS = [
        'create' => "{{ entitydashkeys }}/create",
        'delete' => "{{ entitydashkeys }}/{id}/delete",
        'index'  => "{{ entitydashkeys }}",
        'read'   => "{{ entitydashkeys }}/{id}",
        'update' => "{{ entitydashkeys }}/{id}/update",
    ];

    /**
     * @var array<non-empty-string, array{route: non-empty-string, parameters: array<string, non-empty-string>}>
     */
    private const array DEFAULT_REDIRECTIONS = [
        'create' => [
            'route'      => "{{ entity_key }}.read",
            'parameters' => ['id' => '{{ _entity.id }}'],
        ],
        'delete' => [
            'route'      => "{{ entity_key }}.index",
            'parameters' => [],
        ],
        'update' => [
            'route'      => "{{ entity_key }}.read",
            'parameters' => ['id' => '{{ _entity.id }}'],
        ],
    ];

    /**
     * @var non-empty-string
     */
    private const string DEFAULT_VIEW_PATH = "{{ entity_key }}/{{ action_key }}.html.twig";

    /**
     * @var list<non-empty-string>
     */
    private const array DEFAULT_FORM_TYPES = [
        "App\\Form\\{{ EntityKey }}\\{{ ActionKey }}Type",
        "App\\Form\\{{ EntityKey }}{{ ActionKey }}Type",
        "App\\Form\\{{ EntityKey }}Type",
    ];

    /**
     * @var list<non-empty-string>
     */
    private const array DEFAULT_HELPERS = [
        "App\\Controller\\{{ EntityKey }}\\{{ ActionKey }}ActionHelper",
        "App\\Controller\\{{ EntityKey }}{{ ActionKey }}ActionHelper",
    ];

    public function __construct(
        private SchemaValueExpander $schemaValueExpander,
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
        $schema = $this->getMap($config, 'schema');

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
            'formTypeClass' => $this->resolveFormTypeClass($schema, $keys, $entityClass, $action, $actionConfig),
            'formFallback'  => $this->resolveFormFallback($schema),
            'helperClass'   => $this->resolveHelperClass($schema, $keys, $entityClass, $action, $actionConfig),
            'route'         => $this->resolveRoute($schema, $keys, $entityClass, $action, $actionConfig),
            'redirection'   => $this->resolveRedirection($schema, $keys, $entityClass, $action, $actionConfig),
            'view'          => $this->resolveView($schema, $keys, $entityClass, $action, $actionConfig),
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
        $patterns = array_merge(self::DEFAULT_KEYS, $this->getMap($schema, 'keys'));

        $keys = [];

        foreach ($patterns as $name => $pattern) {
            $value = $this->expand($pattern, [], $entityClass, $action);

            Assert::stringNotEmpty($value);

            $keys[$name] = $value;
        }

        return $keys;
    }

    /**
     * @param array<string, mixed>                       $schema
     * @param array<non-empty-string, non-empty-string>  $keys
     * @param class-string                               $entityClass
     * @param non-empty-string                           $action
     * @param array<string, mixed>                       $actionConfig
     *
     * @return class-string<FormTypeInterface>|null
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function resolveFormTypeClass(
        array $schema,
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?string {
        if (array_key_exists('formType', $actionConfig) && null !== $actionConfig['formType']) {
            $formTypeClass = $actionConfig['formType'];

            Assert::string($formTypeClass);
            Assert::classExists($formTypeClass);
            Assert::subclassOf($formTypeClass, FormTypeInterface::class);

            return $formTypeClass;
        }

        foreach ($this->getPatterns($schema, 'formType', self::DEFAULT_FORM_TYPES) as $pattern) {
            $class = $this->expand($pattern, $keys, $entityClass, $action);

            if (class_exists($class) && is_subclass_of($class, FormTypeInterface::class)) {
                return $class;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed>                       $schema
     * @param array<non-empty-string, non-empty-string>  $keys
     * @param class-string                               $entityClass
     * @param non-empty-string                           $action
     * @param array<string, mixed>                       $actionConfig
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

        foreach ($this->getPatterns($schema, 'helper', self::DEFAULT_HELPERS) as $pattern) {
            $class = $this->expand($pattern, $keys, $entityClass, $action);

            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed>                       $schema
     * @param array<non-empty-string, non-empty-string>  $keys
     * @param class-string                               $entityClass
     * @param non-empty-string                           $action
     * @param array<string, mixed>                       $actionConfig
     *
     * @return ResolvedRoute
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    private function resolveRoute(
        array $schema,
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): array {
        $routeConfig  = $this->getMap($actionConfig, 'route');
        $schemaRoute  = $this->getMap($schema, 'route');

        $name = $this->expandOverridable($routeConfig, 'name', $schemaRoute, 'name', self::DEFAULT_ROUTE_NAME, $keys, $entityClass, $action);

        return [
            'name'         => $name,
            'path'         => $this->resolveRoutePath($routeConfig, $schemaRoute, $keys, $entityClass, $action),
            'requirements' => $this->resolveRequirements($routeConfig),
        ];
    }

    /**
     * @param array<string, mixed>                       $routeConfig
     * @param array<string, mixed>                       $schemaRoute
     * @param array<non-empty-string, non-empty-string>  $keys
     * @param class-string                               $entityClass
     * @param non-empty-string                           $action
     *
     * @return non-empty-string
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    private function resolveRoutePath(
        array $routeConfig,
        array $schemaRoute,
        array $keys,
        string $entityClass,
        string $action,
    ): string {
        if (array_key_exists('path', $routeConfig)) {
            Assert::stringNotEmpty($routeConfig['path']);

            return $routeConfig['path'];
        }

        /** @var array<non-empty-string, non-empty-string> $paths */
        $paths = array_merge(self::DEFAULT_ROUTE_PATHS, $this->getMap($schemaRoute, 'paths'));

        if (!array_key_exists($action, $paths)) {
            throw new CrudEngineMissingConfigurationException($entityClass, $action, 'route.path');
        }

        $path = $this->expand($paths[$action], $keys, $entityClass, $action);

        Assert::stringNotEmpty($path);

        return $path;
    }

    /**
     * @param array<string, mixed> $routeConfig
     *
     * @return array<non-empty-string, non-empty-string>
     */
    private function resolveRequirements(array $routeConfig): array
    {
        $requirements = [];

        foreach ($this->getMap($routeConfig, 'requirements') as $key => $requirement) {
            Assert::stringNotEmpty($key);
            Assert::stringNotEmpty($requirement);

            $requirements[$key] = $requirement;
        }

        return $requirements;
    }

    /**
     * @param array<string, mixed>                       $schema
     * @param array<non-empty-string, non-empty-string>  $keys
     * @param class-string                               $entityClass
     * @param non-empty-string                           $action
     * @param array<string, mixed>                       $actionConfig
     *
     * @return ResolvedRedirection|null
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    private function resolveRedirection(
        array $schema,
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?array {
        if (array_key_exists('redirection', $actionConfig)) {
            $redirectionConfig = $actionConfig['redirection'];
            Assert::isMap($redirectionConfig);

            if (!array_key_exists('route', $redirectionConfig)) {
                throw new CrudEngineMissingConfigurationException($entityClass, $action, 'redirection.route');
            }

            Assert::stringNotEmpty($redirectionConfig['route']);

            return [
                'route'      => $redirectionConfig['route'],
                'parameters' => $this->getStringMap($redirectionConfig, 'parameters'),
                'fragment'   => $this->getNullableString($redirectionConfig, 'fragment'),
            ];
        }

        /** @var array<non-empty-string, array{route: non-empty-string, parameters: array<string, non-empty-string>}> $redirections */
        $redirections = array_merge(self::DEFAULT_REDIRECTIONS, $this->getMap($schema, 'redirection'));

        if (!array_key_exists($action, $redirections)) {
            return null;
        }

        $schemaRedirection = $redirections[$action];
        Assert::isMap($schemaRedirection);
        Assert::keyExists($schemaRedirection, 'route');
        Assert::stringNotEmpty($schemaRedirection['route']);

        $route = $this->expand($schemaRedirection['route'], $keys, $entityClass, $action);
        Assert::stringNotEmpty($route);

        return [
            'route'      => $route,
            'parameters' => $this->getStringMap($schemaRedirection, 'parameters'),
            'fragment'   => null,
        ];
    }

    /**
     * @param array<string, mixed>                       $schema
     * @param array<non-empty-string, non-empty-string>  $keys
     * @param class-string                               $entityClass
     * @param non-empty-string                           $action
     * @param array<string, mixed>                       $actionConfig
     *
     * @return ResolvedView
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function resolveView(
        array $schema,
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): array {
        $viewConfig   = $this->getMap($actionConfig, 'view');
        $schemaView   = $this->getMap($schema, 'view');

        return [
            'path'      => $this->expandOverridable($viewConfig, 'path', $schemaView, 'path', self::DEFAULT_VIEW_PATH, $keys, $entityClass, $action),
            'variables' => $this->resolveViewVariables($schemaView, $viewConfig, $keys, $entityClass, $action),
            'fallback'  => $this->resolveViewFallback($schemaView),
        ];
    }

    /**
     * @param array<string, mixed>                       $schemaView
     * @param array<string, mixed>                       $viewConfig
     * @param array<non-empty-string, non-empty-string>  $keys
     * @param class-string                               $entityClass
     * @param non-empty-string                           $action
     *
     * @return array<non-empty-string, list<non-empty-string>>
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function resolveViewVariables(
        array $schemaView,
        array $viewConfig,
        array $keys,
        string $entityClass,
        string $action,
    ): array {
        $variables = [];

        foreach ($this->getMap($schemaView, 'variables') as $name => $values) {
            Assert::stringNotEmpty($name);

            $expanded = [];

            foreach ($this->toList($values) as $value) {
                $value = $this->expand($value, $keys, $entityClass, $action);

                Assert::stringNotEmpty($value);

                $expanded[] = $value;
            }

            $variables[$name] = $expanded;
        }

        foreach ($this->getMap($viewConfig, 'variables') as $name => $values) {
            Assert::stringNotEmpty($name);

            $variables[$name] = $this->toList($values);
        }

        return $variables;
    }

    /**
     * @param array<string, mixed> $schemaView
     *
     * @return non-empty-string
     */
    private function resolveViewFallback(array $schemaView): string
    {
        if (!array_key_exists('fallback', $schemaView)) {
            return ViewFallbackMode::BUILT_IN->value;
        }

        Assert::stringNotEmpty($schemaView['fallback']);

        $fallback = ViewFallbackMode::tryFrom($schemaView['fallback']);

        Assert::notNull(
            $fallback,
            sprintf('Unknown view fallback mode "%s".', $schemaView['fallback']),
        );

        return $fallback->value;
    }

    /**
     * @param array<string, mixed> $schema
     *
     * @return non-empty-string
     */
    private function resolveFormFallback(array $schema): string
    {
        $schemaForm = $this->getMap($schema, 'form');

        if (!array_key_exists('fallback', $schemaForm)) {
            return FormFallbackMode::BUILT_IN->value;
        }

        Assert::stringNotEmpty($schemaForm['fallback']);

        $fallback = FormFallbackMode::tryFrom($schemaForm['fallback']);

        Assert::notNull(
            $fallback,
            sprintf('Unknown form fallback mode "%s".', $schemaForm['fallback']),
        );

        return $fallback->value;
    }

    /**
     * Resolves a value that may be set on the action, then the schema, then a default
     * pattern — expanding placeholders unless it comes from the action override.
     *
     * @param array<string, mixed>                       $config
     * @param non-empty-string                           $configKey
     * @param array<string, mixed>                       $schema
     * @param non-empty-string                           $schemaKey
     * @param non-empty-string                           $default
     * @param array<non-empty-string, non-empty-string>  $keys
     * @param class-string                               $entityClass
     * @param non-empty-string                           $action
     *
     * @return non-empty-string
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function expandOverridable(
        array $config,
        string $configKey,
        array $schema,
        string $schemaKey,
        string $default,
        array $keys,
        string $entityClass,
        string $action,
    ): string {
        if (array_key_exists($configKey, $config)) {
            Assert::stringNotEmpty($config[$configKey]);

            return $config[$configKey];
        }

        $pattern = $default;

        if (array_key_exists($schemaKey, $schema)) {
            Assert::stringNotEmpty($schema[$schemaKey]);

            $pattern = $schema[$schemaKey];
        }

        $value = $this->expand($pattern, $keys, $entityClass, $action);

        Assert::stringNotEmpty($value);

        return $value;
    }

    /**
     * @param array<string, mixed>  $config
     * @param non-empty-string      $key
     * @param list<non-empty-string> $default
     *
     * @return list<non-empty-string>
     */
    private function getPatterns(array $config, string $key, array $default): array
    {
        if (!array_key_exists($key, $config)) {
            return $default;
        }

        $patterns = $config[$key];
        Assert::isArray($patterns);

        if ([] === $patterns) {
            return $default;
        }

        Assert::allStringNotEmpty($patterns);

        return array_values($patterns);
    }

    /**
     * @param non-empty-string                           $value
     * @param array<non-empty-string, non-empty-string>  $keys
     * @param class-string                               $entityClass
     * @param non-empty-string                           $action
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function expand(
        string $value,
        array $keys,
        string $entityClass,
        string $action,
    ): string {
        return $this->schemaValueExpander->expand(
            $value,
            $keys,
            [
                'entityClass' => $entityClass,
                'action'      => $action,
            ],
        );
    }

    /**
     * @param array<string, mixed> $config
     * @param non-empty-string     $key
     *
     * @return array<string, mixed>
     */
    private function getMap(array $config, string $key): array
    {
        if (!array_key_exists($key, $config)) {
            return [];
        }

        $value = $config[$key];
        Assert::isMap($value);

        return $value;
    }

    /**
     * @param array<array-key, mixed> $config
     * @param non-empty-string        $key
     *
     * @return array<string, string>
     */
    private function getStringMap(array $config, string $key): array
    {
        if (!array_key_exists($key, $config)) {
            return [];
        }

        $value = $config[$key];
        Assert::isMap($value);

        $result = [];

        foreach ($value as $k => $v) {
            Assert::string($k);
            Assert::string($v);

            $result[$k] = $v;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $config
     * @param non-empty-string     $key
     */
    private function getNullableString(array $config, string $key): ?string
    {
        if (!array_key_exists($key, $config) || null === $config[$key]) {
            return null;
        }

        Assert::string($config[$key]);

        return $config[$key];
    }

    /**
     * @return list<non-empty-string>
     */
    private function toList(mixed $values): array
    {
        if (!is_iterable($values)) {
            $values = [$values];
        }

        $list = [];

        foreach ($values as $value) {
            Assert::stringNotEmpty($value);

            $list[] = $value;
        }

        return $list;
    }
}
