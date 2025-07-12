<?php

namespace Jmf\CrudEngine\Configuration\Schema;

use Jmf\CrudEngine\Configuration\Schema\FormType\FormTypeSchema;
use Jmf\CrudEngine\Configuration\Schema\FormType\FormTypeSchemaLoader;
use Jmf\CrudEngine\Configuration\Schema\Helper\HelperSchema;
use Jmf\CrudEngine\Configuration\Schema\Helper\HelperSchemaLoader;
use Jmf\CrudEngine\Configuration\Schema\Route\RouteSchema;
use Jmf\CrudEngine\Configuration\Schema\Route\RouteSchemaLoader;
use Jmf\CrudEngine\Configuration\Schema\View\ViewSchema;
use Jmf\CrudEngine\Configuration\Schema\View\ViewSchemaLoader;
use Webmozart\Assert\Assert;

readonly class SchemaLoader
{
    public function __construct(
        private HelperSchemaLoader $helpersLoader,
        private FormTypeSchemaLoader $formTypesLoader,
        private RouteSchemaLoader $routeLoader,
        private ViewSchemaLoader $viewLoader,
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
    ): FormTypeSchema {
        return $this->formTypesLoader->load($schemaConfig);
    }

    /**
     * @param array<string, mixed> $schemaConfig
     */
    private function getHelpers(
        array $schemaConfig,
    ): HelperSchema {
        return $this->helpersLoader->load($schemaConfig);
    }

    /**
     * @param array<string, mixed> $schemaConfig
     */
    private function getRoute(
        array $schemaConfig,
    ): RouteSchema {
        return $this->routeLoader->load($schemaConfig);
    }

    /**
     * @param array<string, mixed> $schemaConfig
     */
    private function getView(
        array $schemaConfig,
    ): ViewSchema {
        return $this->viewLoader->load($schemaConfig);
    }
}
