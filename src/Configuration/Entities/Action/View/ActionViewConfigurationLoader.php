<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\View;

use Jmf\CrudEngine\Configuration\Entities\Action\View\Path\ActionViewPathResolver;
use Jmf\CrudEngine\Configuration\Entities\Action\View\Variables\ActionViewVariablesCollection;
use Jmf\CrudEngine\Configuration\Entities\Action\View\Variables\ActionViewVariablesResolver;
use Jmf\CrudEngine\Configuration\Schema\View\ViewSchema;
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
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $actionConfig
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function load(
        ViewSchema $viewSchema,
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ActionViewConfiguration {
        $viewConfig = [];

        if (array_key_exists('view', $actionConfig)) {
            Assert::isMap($actionConfig['view']);

            $viewConfig = $actionConfig['view'];
        }

        return new ActionViewConfiguration(
            $this->getPath($viewSchema, $keys, $entityClass, $action, $viewConfig),
            $this->getVariables($viewSchema, $keys, $entityClass, $action, $viewConfig),
            $viewSchema->getViewFallbackMode(),
        );
    }

    /**
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $viewConfig
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function getPath(
        ViewSchema $viewSchema,
        array $keys,
        string $entityClass,
        string $action,
        array $viewConfig,
    ): string {
        return $this->pathResolver->resolve(
            $viewSchema,
            $keys,
            $entityClass,
            $action,
            $viewConfig,
        );
    }

    /**
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $viewConfig
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function getVariables(
        ViewSchema $viewSchema,
        array $keys,
        string $entityClass,
        string $action,
        array $viewConfig,
    ): ActionViewVariablesCollection {
        return $this->variablesResolver->resolve(
            $viewSchema,
            $keys,
            $entityClass,
            $action,
            $viewConfig,
        );
    }
}
