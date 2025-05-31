<?php

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Webmozart\Assert\Assert;

readonly class ViewConfigurationLoader
{
    /**
     * @param class-string         $entityClass
     * @param array<string, mixed> $actionConfig
     *
     * @throws CrudEngineMissingConfigurationException
     */
    public function load(
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?ViewConfiguration {
        if (!array_key_exists('view', $actionConfig)) {
            return null;
        }

        Assert::isMap($actionConfig['view']);

        $viewConfig = $actionConfig['view'];

        return new ViewConfiguration(
            $this->getPath($entityClass, $action, $viewConfig),
            $this->getVariables($viewConfig),
        );
    }

    /**
     * @param class-string         $entityClass
     * @param array<string, mixed> $viewConfig
     *
     * @throws CrudEngineMissingConfigurationException
     */
    private function getPath(
        string $entityClass,
        string $action,
        array $viewConfig,
    ): string {
        if (!array_key_exists('path', $viewConfig)) {
            throw new CrudEngineMissingConfigurationException(
                $entityClass,
                $action,
                'view.path',
            );
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
