<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\View\Path;

use Jmf\CrudEngine\Configuration\Schema\View\ViewSchema;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Webmozart\Assert\Assert;

readonly class ActionViewPathResolver
{
    public function __construct(
        private SchemaValueExpander $schemaValueExpander,
    ) {
    }

    /**
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $viewConfig
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function resolve(
        ViewSchema $viewSchema,
        array $keys,
        string $entityClass,
        string $action,
        array $viewConfig,
    ): string {
        if (!array_key_exists('path', $viewConfig)) {
            return $this->getFallbackPath($viewSchema, $keys, $entityClass, $action);
        }

        Assert::string($viewConfig['path']);

        return $viewConfig['path'];
    }


    /**
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function getFallbackPath(
        ViewSchema $viewSchema,
        array $keys,
        string $entityClass,
        string $action,
    ): string {
        // @todo Validate file existence.
        return $this->schemaValueExpander->expand(
            $viewSchema->getPath(),
            array_merge(
                $keys,
                [
                    'entityClass' => $entityClass,
                    'action'      => $action,
                ],
            ),
        );
    }
}
