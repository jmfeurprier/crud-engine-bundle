<?php

namespace Jmf\CrudEngine\Configuration\Schema\View;

use Jmf\CrudEngine\Configuration\Schema\View\Variables\SchemaViewVariablesCollection;
use Webmozart\Assert\Assert;

readonly class SchemaViewConfigurationLoader
{
    private const string PATH_DEFAULT = "{{ entityClass|u.afterLast('\\').snake }}/{{ action }}.html.twig";

    /**
     * @param array<string, mixed> $actionConfig
     */
    public function load(
        array $actionConfig,
    ): SchemaViewConfiguration {
        $viewConfig = [];

        if (array_key_exists('view', $actionConfig)) {
            Assert::isMap($actionConfig['view']);

            $viewConfig = $actionConfig['view'];
        }

        return new SchemaViewConfiguration(
            $this->getPath($viewConfig),
            $this->getVariables($viewConfig),
        );
    }

    /**
     * @param array<string, mixed> $viewConfig
     */
    private function getPath(
        array $viewConfig,
    ): string {
        if (!array_key_exists('path', $viewConfig)) {
            return self::PATH_DEFAULT;
        }

        Assert::string($viewConfig['path']);

        return $viewConfig['path'];
    }

    /**
     * @param array<string, mixed> $viewConfig
     */
    private function getVariables(array $viewConfig): SchemaViewVariablesCollection
    {
        if (!array_key_exists('variables', $viewConfig)) {
            return SchemaViewVariablesCollection::createEmpty();
        }

        Assert::isMap($viewConfig['variables']);

        $variables = [];

        foreach ($viewConfig['variables'] as $variableName => $variableValues) {
            Assert::stringNotEmpty($variableName);

            $variables[$variableName] = $this->getVariableValues($variableValues);
        }

        return new SchemaViewVariablesCollection($variables);
    }

    /**
     * @return non-empty-string[]
     */
    private function getVariableValues(mixed $variableValues): iterable
    {
        if (!is_iterable($variableValues)) {
            $variableValues = [$variableValues];
        }

        Assert::allStringNotEmpty($variableValues);

        return $variableValues;
    }
}
