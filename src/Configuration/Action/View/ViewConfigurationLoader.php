<?php

namespace Jmf\CrudEngine\Configuration\Action\View;

use Jmf\CrudEngine\Configuration\EntityConfigurationFallbacksResolver;
use Jmf\CrudEngine\Configuration\KeyStringCollection;
use Webmozart\Assert\Assert;

readonly class ViewConfigurationLoader
{
    public function __construct(
        private EntityConfigurationFallbacksResolver $fallbacksResolver,
    ) {
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     */
    public function load(
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?ViewConfiguration {
        $viewConfig = [];

        if (array_key_exists('view', $actionConfig)) {
            Assert::isMap($actionConfig['view']);

            $viewConfig = $actionConfig['view'];
        }

        return new ViewConfiguration(
            $this->getPath($entityClass, $action, $viewConfig),
            $this->getVariables($viewConfig),
        );
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $viewConfig
     */
    private function getPath(
        string $entityClass,
        string $action,
        array $viewConfig,
    ): string {
        if (!array_key_exists('path', $viewConfig)) {
            return $this->fallbacksResolver->resolveViewPath($entityClass, $action);
        }

        Assert::string($viewConfig['path']);

        return $viewConfig['path'];
    }

    /**
     * @param array<string, mixed> $viewConfig
     */
    private function getVariables(array $viewConfig): KeyStringCollection
    {
        if (!array_key_exists('variables', $viewConfig)) {
            return KeyStringCollection::createEmpty();
        }

        Assert::isMap($viewConfig['variables']);
        Assert::allString($viewConfig['variables']);

        return new KeyStringCollection(
            $viewConfig['variables'],
        );
    }
}
