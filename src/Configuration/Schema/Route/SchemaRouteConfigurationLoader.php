<?php

namespace Jmf\CrudEngine\Configuration\Schema\Route;

use Jmf\CrudEngine\Configuration\Schema\Route\Paths\SchemaRoutePathsCollection;
use Webmozart\Assert\Assert;

readonly class SchemaRouteConfigurationLoader
{
    /**
     * @param array<string, mixed> $schemaConfig
     */
    public function load(array $schemaConfig): SchemaRouteConfiguration
    {
        $routeConfig = $this->getRouteConfig($schemaConfig);

        return new SchemaRouteConfiguration(
            $this->getName($routeConfig),
            $this->getPaths($routeConfig),
        );
    }

    /**
     * @param array<string, mixed> $schemaConfig
     *
     * @return array<string, mixed>
     */
    private function getRouteConfig(array $schemaConfig): array
    {
        if (!array_key_exists('route', $schemaConfig)) {
            return [];
        }

        $routeConfig = $schemaConfig['route'];

        Assert::isMap($routeConfig);

        return $routeConfig;
    }

    /**
     * @param array<string, mixed> $routeConfig
     *
     * @return non-empty-string
     */
    private function getName(
        array $routeConfig,
    ): string {
        if (!array_key_exists('name', $routeConfig)) {
            return SchemaRouteConfiguration::DEFAULT_NAME;
        }

        $name = $routeConfig['name'];

        Assert::stringNotEmpty($name);

        return $name;
    }

    /**
     * @param array<string, mixed> $routeConfig
     */
    private function getPaths(
        array $routeConfig,
    ): SchemaRoutePathsCollection {
        if (!array_key_exists('paths', $routeConfig)) {
            return SchemaRoutePathsCollection::createEmpty();
        }

        $pathsConfig = $routeConfig['paths'];

        Assert::isMap($pathsConfig);

        $paths = [];

        foreach ($pathsConfig as $action => $path) {
            Assert::stringNotEmpty($action);
            Assert::string($path);

            $paths[$action] = $path;
        }

        return new SchemaRoutePathsCollection($paths);
    }
}
