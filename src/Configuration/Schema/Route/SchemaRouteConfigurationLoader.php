<?php

namespace Jmf\CrudEngine\Configuration\Schema\Route;

use Webmozart\Assert\Assert;

readonly class SchemaRouteConfigurationLoader
{
    private const string ROUTE_NAME_DEFAULT = "{{ entityClass|u.afterLast('\\').snake }}.{{ action }}";

    /**
     * @param array<string, mixed> $schemaConfig
     */
    public function load(array $schemaConfig): SchemaRouteConfiguration
    {
        $routeConfig = $this->getRouteConfig($schemaConfig);

        return new SchemaRouteConfiguration(
            $this->getName($routeConfig),
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
            return self::ROUTE_NAME_DEFAULT;
        }

        $name = $routeConfig['name'];

        Assert::stringNotEmpty($name);

        return $name;
    }
}
