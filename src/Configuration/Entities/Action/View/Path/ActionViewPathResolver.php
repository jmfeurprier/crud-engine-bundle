<?php

namespace Jmf\CrudEngine\Configuration\Entities\Action\View\Path;

use Jmf\CrudEngine\Configuration\Schema\Schema;
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
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $viewConfig
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function resolve(
        Schema $schema,
        string $entityClass,
        string $action,
        array $viewConfig,
    ): string {
        if (!array_key_exists('path', $viewConfig)) {
            return $this->getFallbackPath($schema, $entityClass, $action);
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
        Schema $schema,
        string $entityClass,
        string $action,
    ): string {
        // @todo Validate file existence.
        return $this->schemaValueExpander->expand(
            $schema->getView()->getPath(),
            [
                'entityClass' => $entityClass,
                'action'      => $action,
            ],
        );
    }
}
