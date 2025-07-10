<?php

namespace Jmf\CrudEngine\Configuration\Entities\Action\View;

use Jmf\CrudEngine\Configuration\KeyStringCollection;
use Jmf\CrudEngine\Configuration\Schema\SchemaConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\TemplateRendering\TemplateRenderer;
use Throwable;
use Webmozart\Assert\Assert;

readonly class ActionViewConfigurationLoader
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
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
            $this->getVariables($viewConfig),
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
        if (!array_key_exists('path', $viewConfig)) {
            return $this->getFallbackPath($schemaConfiguration, $entityClass, $action);
        }

        Assert::string($viewConfig['path']);

        return $viewConfig['path'];
    }


    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function getFallbackPath(
        SchemaConfiguration $schemaConfiguration,
        string $entityClass,
        string $action,
    ): string {
        // @todo Validate file existence.

        try {
            return $this->templateRenderer->renderFromString(
                $schemaConfiguration->getViewConfiguration()->getPath(),
                [
                    'entityClass' => $entityClass,
                    'action'      => $action,
                ],
            );
        } catch (Throwable $e) {
            // @todo Add context.
            throw new CrudEngineInvalidConfigurationException();
        }
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
