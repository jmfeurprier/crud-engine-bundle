<?php

namespace Jmf\CrudEngine\Configuration\Schema;

use Jmf\CrudEngine\Configuration\Schema\FormType\SchemaFormTypesCollection;
use Jmf\CrudEngine\Configuration\Schema\FormType\SchemaFormTypesLoader;
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
        private SchemaHelpersLoader $helpersLoader,
        private SchemaFormTypesLoader $formTypesLoader,
        private SchemaRouteLoader $routeLoader,
        private SchemaViewLoader $viewLoader,
    ) {
    }

    /**
     * @param array<string, mixed> $config
     */
    public function load(array $config): Schema
    {
        $schemaConfig = $this->getSchemaConfig($config);

        return new Schema(
            $this->getFormTypes($schemaConfig),
            $this->getHelpers($schemaConfig),
            $this->getRoute($schemaConfig),
            $this->getView($schemaConfig),
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
    private function getFormTypes(
        array $schemaConfig,
    ): SchemaFormTypesCollection {
        return $this->formTypesLoader->load($schemaConfig);
    }

    /**
     * @param array<string, mixed> $schemaConfig
     */
    private function getHelpers(
        array $schemaConfig,
    ): SchemaHelpersCollection {
        return $this->helpersLoader->load($schemaConfig);
    }

    /**
     * @param array<string, mixed> $schemaConfig
     */
    private function getRoute(
        array $schemaConfig,
    ): SchemaRoute {
        return $this->routeLoader->load($schemaConfig);
    }

    /**
     * @param array<string, mixed> $schemaConfig
     */
    private function getView(
        array $schemaConfig,
    ): SchemaView {
        return $this->viewLoader->load($schemaConfig);
    }
}
