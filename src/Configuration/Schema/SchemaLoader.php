<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Schema;

use Jmf\CrudEngine\Configuration\Schema\FormType\FormTypeSchemaLoader;
use Jmf\CrudEngine\Configuration\Schema\Helper\HelperSchemaLoader;
use Jmf\CrudEngine\Configuration\Schema\Keys\KeySchemaLoader;
use Jmf\CrudEngine\Configuration\Schema\Route\RouteSchemaLoader;
use Jmf\CrudEngine\Configuration\Schema\View\ViewSchemaLoader;
use Webmozart\Assert\Assert;

readonly class SchemaLoader
{
    public function __construct(
        private KeySchemaLoader $keySchemaLoader,
        private HelperSchemaLoader $helperSchemaLoader,
        private FormTypeSchemaLoader $formTypeSchemaLoader,
        private RouteSchemaLoader $routeSchemaLoader,
        private ViewSchemaLoader $viewSchemaLoader,
    ) {
    }

    /**
     * @param array<string, mixed> $config
     */
    public function load(array $config): Schema
    {
        $schemaConfig = $this->getSchemaConfig($config);

        return new Schema(
            $this->keySchemaLoader->load($schemaConfig),
            $this->formTypeSchemaLoader->load($schemaConfig),
            $this->helperSchemaLoader->load($schemaConfig),
            $this->routeSchemaLoader->load($schemaConfig),
            $this->viewSchemaLoader->load($schemaConfig),
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
}
