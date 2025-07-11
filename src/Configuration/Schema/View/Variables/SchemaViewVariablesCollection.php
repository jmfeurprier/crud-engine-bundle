<?php

namespace Jmf\CrudEngine\Configuration\Schema\View\Variables;

use Webmozart\Assert\Assert;

readonly class SchemaViewVariablesCollection
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
     * @return array<non-empty-string, iterable<non-empty-string>>
     */
    public function all(): array
    {
        return $this->values;
    }
}
