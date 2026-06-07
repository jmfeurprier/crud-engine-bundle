<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation;

use Jmf\CrudEngine\Compilation\Resolution\ConfigurationValueResolver;
use Jmf\CrudEngine\Compilation\Resolution\MapResolver;
use Jmf\CrudEngine\Compilation\Resolution\OverridableConfigurationValueResolver;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type CompiledRoute array{name: non-empty-string, path: non-empty-string, requirements: array<non-empty-string, non-empty-string>}
 */
readonly class RouteDefinitionCompiler
{
    /**
     * @var non-empty-string
     */
    private const string DEFAULT_NAME = "{{ entity_key }}.{{ action_key }}";

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
     * @param non-empty-string                          $defaultPath
     *
     * @return CompiledRoute
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function compile(
        array $schema,
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
        string $defaultPath,
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
            'path'         => $this->resolvePath($routeConfig, $schemaRoute, $keys, $entityClass, $action, $defaultPath),
            'requirements' => $this->resolveRequirements($routeConfig),
        ];
    }

    /**
     * @param array<string, mixed>                      $routeConfig
     * @param array<string, mixed>                      $schemaRoute
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param non-empty-string                          $defaultPath
     *
     * @return non-empty-string
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function resolvePath(
        array $routeConfig,
        array $schemaRoute,
        array $keys,
        string $entityClass,
        string $action,
        string $defaultPath,
    ): string {
        if (array_key_exists('path', $routeConfig)) {
            Assert::stringNotEmpty($routeConfig['path']);

            return $routeConfig['path'];
        }

        $schemaPaths = $this->mapResolver->resolve($schemaRoute, 'paths');

        $pattern = array_key_exists($action, $schemaPaths)
            ? $schemaPaths[$action]
            : $defaultPath;

        Assert::stringNotEmpty($pattern);

        $path = $this->configurationValueResolver->resolve(
            $pattern,
            $keys,
            $entityClass,
            $action,
        );

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
