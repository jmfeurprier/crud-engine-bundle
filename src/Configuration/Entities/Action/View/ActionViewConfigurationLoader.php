<?php

namespace Jmf\CrudEngine\Configuration\Entities\Action\View;

use Jmf\CrudEngine\Configuration\Entities\Action\View\Path\ActionViewPathResolver;
use Jmf\CrudEngine\Configuration\Entities\Action\View\Variables\ActionViewVariablesCollection;
use Jmf\CrudEngine\Configuration\Entities\Action\View\Variables\ActionViewVariablesResolver;
use Jmf\CrudEngine\Configuration\Schema\SchemaConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Webmozart\Assert\Assert;

readonly class ActionViewConfigurationLoader
{
    public function __construct(
        private ActionViewVariablesResolver $variablesResolver,
        private ActionViewPathResolver $pathResolver,
    ) {
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function load(
        SchemaConfiguration $schemaConfiguration,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?ActionViewConfiguration {
        $viewConfig = [];

        if (array_key_exists('view', $actionConfig)) {
            Assert::isMap($actionConfig['view']);

            $viewConfig = $actionConfig['view'];
        }

        return new ActionViewConfiguration(
            $this->getPath($schemaConfiguration, $entityClass, $action, $viewConfig),
            $this->getVariables($schemaConfiguration, $entityClass, $action, $viewConfig),
        );
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $viewConfig
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function getPath(
        SchemaConfiguration $schemaConfiguration,
        string $entityClass,
        string $action,
        array $viewConfig,
    ): string {
        return $this->pathResolver->resolve(
            $schemaConfiguration,
            $entityClass,
            $action,
            $viewConfig,
        );
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $viewConfig
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function getVariables(
        SchemaConfiguration $schemaConfiguration,
        string $entityClass,
        string $action,
        array $viewConfig,
    ): ActionViewVariablesCollection {
        return $this->variablesResolver->resolve(
            $schemaConfiguration,
            $entityClass,
            $action,
            $viewConfig,
        );
    }
}
