<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\View\Variables;

use Jmf\CrudEngine\Configuration\Schema\Schema;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Webmozart\Assert\Assert;

readonly class ActionViewVariablesResolver
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
    ): ActionViewVariablesCollection {
        $variables = $schema->getViewSchema()->getVariables()->expand(
            $this->schemaValueExpander,
            [
                'entityClass' => $entityClass,
                'action'      => $action,
            ],
        );

        if (array_key_exists('variables', $viewConfig)) {
            $variablesConfig = $viewConfig['variables'];

            Assert::isMap($variablesConfig);

            foreach ($variablesConfig as $variableName => $variableValues) {
                Assert::stringNotEmpty($variableName);

                $variables[$variableName] = $this->getVariableValues($variableValues);
            }
        }

        return new ActionViewVariablesCollection(
            $variables,
        );
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
