<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Resolution;

use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type ResolvedRoute array{name: non-empty-string, path: non-empty-string, requirements: array<non-empty-string, non-empty-string>}
 */
readonly class RouteConfigurationResolver
{
    /**
     * @var non-empty-string
     */
    private const string DEFAULT_NAME = "{{ entity_key }}.{{ action_key }}";

    /**
     * @var array<non-empty-string, non-empty-string>
     */
    private const array DEFAULT_PATHS = [
        'create' => "{{ entitydashkeys }}/create",
        'delete' => "{{ entitydashkeys }}/{id}/delete",
        'index'  => "{{ entitydashkeys }}",
        'read'   => "{{ entitydashkeys }}/{id}",
        'update' => "{{ entitydashkeys }}/{id}/update",
    ];

    public function __construct(
        private ConfigurationValueResolver $configurationValueResolver,
        private MapResolver $mapResolver,
        private OverridableConfigurationValueResolver $overridableConfigurationValueResolver,
    ) {
    }

    /**
     * @param array<string, mixed>                      $schema
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $actionConfig
     *
     * @return ResolvedRoute
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    public function resolve(
        array $schema,
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): array {
        $routeConfig = $this->mapResolver->resolve($actionConfig, 'route');
        $schemaRoute = $this->mapResolver->resolve($schema, 'route');

        return [
            'name'         => $this->overridableConfigurationValueResolver->resolve(
                config:      $routeConfig,
                configKey:   'name',
                schema:      $schemaRoute,
                schemaKey:   'name',
                default:     self::DEFAULT_NAME,
                keys:        $keys,
                entityClass: $entityClass,
                action:      $action,
            ),
            'path'         => $this->resolvePath($routeConfig, $schemaRoute, $keys, $entityClass, $action),
            'requirements' => $this->resolveRequirements($routeConfig),
        ];
    }

    /**
     * @param array<string, mixed>                      $routeConfig
     * @param array<string, mixed>                      $schemaRoute
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     *
     * @return non-empty-string
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    private function resolvePath(
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
        $paths = array_merge(
            self::DEFAULT_PATHS,
            $this->mapResolver->resolve($schemaRoute, 'paths'),
        );

        if (!array_key_exists($action, $paths)) {
            throw new CrudEngineMissingConfigurationException($entityClass, $action, 'route.path');
        }

        $path = $this->configurationValueResolver->resolve($paths[$action], $keys, $entityClass, $action);

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

        foreach ($this->mapResolver->resolve($routeConfig, 'requirements') as $key => $requirement) {
            Assert::stringNotEmpty($key);
            Assert::stringNotEmpty($requirement);

            $requirements[$key] = $requirement;
        }

        return $requirements;
    }
}
