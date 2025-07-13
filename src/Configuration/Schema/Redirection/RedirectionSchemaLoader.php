<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Schema\Redirection;

use Webmozart\Assert\Assert;

readonly class RedirectionSchemaLoader
{
    /**
     * @param array<string, mixed> $schemaConfig
     */
    public function load(array $schemaConfig): RedirectionSchema
    {
        $redirectionConfig = $this->getRedirectionConfig($schemaConfig);

        $routeSchemas = [];

        foreach ($redirectionConfig as $action => $redirectionRouteConfig) {
            Assert::stringNotEmpty($action);
            Assert::isMap($redirectionRouteConfig);

            Assert::keyExists($redirectionRouteConfig, 'route');
            $route = $redirectionRouteConfig['route'];
            Assert::stringNotEmpty($route);

            $parameters = $redirectionRouteConfig['parameters'] ?? [];
            Assert::isMap($parameters);
            Assert::allStringNotEmpty($parameters);

            $routeSchemas[$action] = new RedirectionRouteSchema(
                $route,
                $parameters,
            );
        }

        return new RedirectionSchema(
            $routeSchemas,
        );
    }

    /**
     * @param array<string, mixed> $schemaConfig
     *
     * @return array<string, mixed>
     */
    private function getRedirectionConfig(array $schemaConfig): array
    {
        if (!array_key_exists('redirection', $schemaConfig)) {
            return [];
        }

        $redirectionConfig = $schemaConfig['redirection'];

        Assert::isMap($redirectionConfig);

        return $redirectionConfig;
    }
}
