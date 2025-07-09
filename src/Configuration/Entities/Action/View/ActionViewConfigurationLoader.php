<?php

namespace Jmf\CrudEngine\Configuration\Entities\Action\View;

use Jmf\CrudEngine\Configuration\KeyStringCollection;
use Jmf\CrudEngine\Configuration\Schema\SchemaConfiguration;
use Webmozart\Assert\Assert;
use function Symfony\Component\String\u;

readonly class ActionViewConfigurationLoader
{
    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
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
            return $this->getFallbackPath($entityClass, $action);
        }

        Assert::string($viewConfig['path']);

        return $viewConfig['path'];
    }


    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     */
    private function getFallbackPath(
        string $entityClass,
        string $action,
    ): string {
        return u($entityClass)->afterLast('\\')->snake()->append("/{$action}.html.twig")->toString();
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
