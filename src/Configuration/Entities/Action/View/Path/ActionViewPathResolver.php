<?php

namespace Jmf\CrudEngine\Configuration\Entities\Action\View\Path;

use Jmf\CrudEngine\Configuration\Schema\SchemaConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Throwable;
use Webmozart\Assert\Assert;

readonly class ActionViewPathResolver
{
    public function __construct(
        private TemplateRendererInterface $templateRenderer,
    ) {
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $viewConfig
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function resolve(
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
}
