<?php

namespace Jmf\CrudEngine\Configuration\Schema;

use Jmf\CrudEngine\Configuration\Schema\Helper\SchemaHelpersCollection;
use Jmf\CrudEngine\Configuration\Schema\Helper\SchemaHelpersLoader;
use Jmf\CrudEngine\Configuration\Schema\Route\SchemaRoute;
use Jmf\CrudEngine\Configuration\Schema\Route\SchemaRouteLoader;
use Jmf\CrudEngine\Configuration\Schema\View\SchemaView;
use Jmf\CrudEngine\Configuration\Schema\View\SchemaViewLoader;
use Webmozart\Assert\Assert;

readonly class SchemaLoader
{
    public function __construct(
        private SchemaHelpersLoader $helperConfigurationLoader,
        private SchemaRouteLoader $routeConfigurationLoader,
        private SchemaViewLoader $viewConfigurationLoader,
    ) {
    }

    /**
     * @param array<string, mixed> $config
     */
    public function load(array $config): Schema
    {
        $schemaConfig = $this->getSchemaConfig($config);

        return new Schema(
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
    ): SchemaHelpersCollection {
        return $this->helperConfigurationLoader->load($schemaConfig);
    }

    /**
     * @param array<string, mixed> $schemaConfig
     */
    private function getRouteConfiguration(
        array $schemaConfig,
    ): SchemaRoute {
        return $this->routeConfigurationLoader->load($schemaConfig);
    }

    /**
     * @param array<string, mixed> $schemaConfig
     */
    private function getViewConfiguration(
        array $schemaConfig,
    ): SchemaView {
        return $this->viewConfigurationLoader->load($schemaConfig);
    }
}
