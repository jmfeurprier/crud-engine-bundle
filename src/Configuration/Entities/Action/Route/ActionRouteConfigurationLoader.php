<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\Route;

use Jmf\CrudEngine\Configuration\Entities\Action\Route\Requirements\ActionRouteRequirementCollection;
use Jmf\CrudEngine\Configuration\Schema\Route\RouteSchema;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Webmozart\Assert\Assert;

readonly class ActionRouteConfigurationLoader
{
    public function __construct(
        private SchemaValueExpander $schemaValueExpander,
    ) {
    }

    /**
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $actionConfig
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    public function load(
        RouteSchema $routeSchema,
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ActionRouteConfiguration {
        $routeConfig = $this->getRouteConfig($actionConfig);

        return new ActionRouteConfiguration(
            $this->getName($routeSchema, $keys, $entityClass, $action, $routeConfig),
            $this->getPath($routeSchema, $keys, $entityClass, $action, $routeConfig),
            $this->getRequirements($routeConfig),
        );
    }

    /**
     * @param array<string, mixed> $actionConfig
     *
     * @return array<string, mixed>
     */
    private function getRouteConfig(array $actionConfig): array
    {
        if (!array_key_exists('route', $actionConfig)) {
            return [];
        }

        $routeConfig = $actionConfig['route'];

        Assert::isMap($routeConfig);

        return $routeConfig;
    }

    /**
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param array<string, mixed>                      $routeConfig
     * @param non-empty-string                          $action
     *
     * @return non-empty-string
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function getName(
        RouteSchema $routeSchema,
        array $keys,
        string $entityClass,
        string $action,
        array $routeConfig,
    ): string {
        if (!array_key_exists('name', $routeConfig)) {
            return $this->getFallbackName(
                $routeSchema,
                $keys,
                $entityClass,
                $action,
            );
        }

        Assert::stringNotEmpty($routeConfig['name']);

        return $routeConfig['name'];
    }

    /**
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     *
     * @return non-empty-string
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function getFallbackName(
        RouteSchema $routeSchema,
        array $keys,
        string $entityClass,
        string $action,
    ): string {
        $name = $this->schemaValueExpander->expand(
            $routeSchema->getName(),
            $keys,
            [
                'entityClass' => $entityClass,
                'action'      => $action,
            ],
        );

        Assert::stringNotEmpty($name);

        return $name;
    }

    /**
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $routeConfig
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    private function getPath(
        RouteSchema $routeSchema,
        array $keys,
        string $entityClass,
        string $action,
        array $routeConfig,
    ): string {
        if (array_key_exists('path', $routeConfig)) {
            $path = $routeConfig['path'];

            Assert::string($path);

            return $path;
        }

        return $this->tryGetFallbackPath(
            $routeSchema,
            $keys,
            $entityClass,
            $action,
        )
            ??
            throw new CrudEngineMissingConfigurationException(
                $entityClass,
                $action,
                'route.path',
            );
    }

    /**
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function tryGetFallbackPath(
        RouteSchema $routeSchema,
        array $keys,
        string $entityClass,
        string $action,
    ): ?string {
        $path = $routeSchema->getPaths()->tryGet($action);

        if (null === $path) {
            return null;
        }

        return $this->schemaValueExpander->expand(
            $path,
            $keys,
            [
                'entityClass' => $entityClass,
                'action'      => $action,
            ],
        );
    }

    /**
     * @param array<string, mixed> $routeConfig
     */
    private function getRequirements(
        array $routeConfig,
    ): ActionRouteRequirementCollection {
        if (!array_key_exists('requirements', $routeConfig)) {
            return ActionRouteRequirementCollection::createDefault();
        }

        $requirementsConfig = $routeConfig['requirements'];

        Assert::isMap($requirementsConfig);

        $requirements = [];

        foreach ($requirementsConfig as $key => $requirement) {
            Assert::stringNotEmpty($key);
            Assert::stringNotEmpty($requirement);

            $requirements[$key] = $requirement;
        }

        return new ActionRouteRequirementCollection($requirements);
    }
}
