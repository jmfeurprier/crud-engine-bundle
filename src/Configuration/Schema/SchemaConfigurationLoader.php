<?php

namespace Jmf\CrudEngine\Configuration\Schema;

use Jmf\CrudEngine\Configuration\Schema\Helper\SchemaHelperConfiguration;
use Jmf\CrudEngine\Configuration\Schema\Helper\SchemaHelperConfigurationLoader;
use Jmf\CrudEngine\Configuration\Schema\Route\SchemaRouteConfiguration;
use Jmf\CrudEngine\Configuration\Schema\Route\SchemaRouteConfigurationLoader;
use Jmf\CrudEngine\Configuration\Schema\View\SchemaViewConfiguration;
use Jmf\CrudEngine\Configuration\Schema\View\SchemaViewConfigurationLoader;
use Webmozart\Assert\Assert;

readonly class SchemaConfigurationLoader
{
    public function __construct(
        private SchemaHelperConfigurationLoader $helperConfigurationLoader,
        private SchemaRouteConfigurationLoader $routeConfigurationLoader,
        private SchemaViewConfigurationLoader $viewConfigurationLoader,
    ) {
    }

    /**
     * @param array<string, mixed> $config
     */
    public function load(array $config): SchemaConfiguration
    {
        $schemaConfig = $this->getSchemaConfig($config);

        return new SchemaConfiguration(
            $this->getHelperConfiguration($schemaConfig),
            $this->getRouteConfiguration($schemaConfig),
            $this->getViewConfiguration($schemaConfig),
        );
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function getSchemaConfig(array $config): array
    {
        if (!array_key_exists('schema', $config)) {
            return [];
        }

        $schemaConfig = $config['schema'];

        Assert::isMap($schemaConfig);

        return $schemaConfig;
    }

    /**
     * @param array<string, mixed> $schemaConfig
     */
    private function getHelperConfiguration(
        array $schemaConfig,
    ): SchemaHelperConfiguration {
        return $this->helperConfigurationLoader->load($schemaConfig);
    }

    /**
     * @param array<string, mixed> $schemaConfig
     */
    private function getRouteConfiguration(
        array $schemaConfig,
    ): SchemaRouteConfiguration {
        return $this->routeConfigurationLoader->load($schemaConfig);
    }

    /**
     * @param array<string, mixed> $schemaConfig
     */
    private function getViewConfiguration(
        array $schemaConfig,
    ): SchemaViewConfiguration {
        return $this->viewConfigurationLoader->load($schemaConfig);
    }
}
