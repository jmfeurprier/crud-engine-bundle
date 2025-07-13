<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Schema\View;

use Jmf\CrudEngine\Configuration\Schema\View\Variables\ViewVariablesSchema;
use Webmozart\Assert\Assert;

readonly class ViewSchemaLoader
{
    /**
     * @param array<string, mixed> $actionConfig
     */
    public function load(
        array $actionConfig,
    ): ViewSchema {
        $viewConfig = [];

        if (array_key_exists('view', $actionConfig)) {
            Assert::isMap($actionConfig['view']);

            $viewConfig = $actionConfig['view'];
        }

        return new ViewSchema(
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
            return ViewSchema::DEFAULT_PATH;
        }

        Assert::string($viewConfig['path']);

        return $viewConfig['path'];
    }

    /**
     * @param array<string, mixed> $viewConfig
     */
    private function getVariables(array $viewConfig): ViewVariablesSchema
    {
        if (!array_key_exists('variables', $viewConfig)) {
            return ViewVariablesSchema::createDefault();
        }

        Assert::isMap($viewConfig['variables']);

        $variables = [];

        foreach ($viewConfig['variables'] as $variableName => $variableValues) {
            Assert::stringNotEmpty($variableName);

            $variables[$variableName] = $this->getVariableValues($variableValues);
        }

        return new ViewVariablesSchema($variables);
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
