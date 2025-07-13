<?php

namespace Jmf\CrudEngine\Configuration\Schema\View\Variables;

use Jmf\CrudEngine\Configuration\Schema\Schema;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Webmozart\Assert\Assert;

readonly class ViewVariablesSchema
{
    public static function createDefault(): self
    {
        return new self([]);
    }

    /**
     * @param array<non-empty-string, iterable<non-empty-string>> $values
     */
    public function __construct(
        private array $values,
    ) {
        Assert::isMap($values);

        foreach ($values as $variable => $value) {
            Assert::stringNotEmpty($variable);
            Assert::allStringNotEmpty($value);
        }
    }

    /**
     * @param array<non-empty-string, mixed> $arguments
     *
     * @return array<non-empty-string, iterable<non-empty-string>>
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function expand(
        SchemaValueExpander $schemaValueExpander,
        array $arguments,
    ): array {
        $variables = [];

        foreach ($this->values as $variableName => $values) {
            $variables[$variableName] = [];

            foreach ($values as $value) {
                $value = $schemaValueExpander->expand(
                    $value,
                    $arguments,
                );

                Assert::stringNotEmpty($value);

                $variables[$variableName][] = $value;
            }
        }

        return $variables;
    }
}
