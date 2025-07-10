<?php

namespace Jmf\CrudEngine\Configuration\Entities\Action\View\Variables;

use Webmozart\Assert\Assert;

readonly class ActionViewVariablesCollection
{
    public static function createEmpty(): self
    {
        return new self([]);
    }

    /**
     * @param array<non-empty-string, iterable<non-empty-string>> $variables
     */
    public function __construct(
        private array $variables,
    ) {
        Assert::isMap($variables);

        foreach ($variables as $variable => $value) {
            Assert::stringNotEmpty($variable);
            Assert::allStringNotEmpty($value);
        }
    }

    /**
     * @return array<non-empty-string, iterable<non-empty-string>>
     */
    public function all(): array
    {
        return $this->variables;
    }

    /**
     * @param non-empty-string $variable
     *
     * @return non-empty-string[]
     */
    public function tryGet(string $variable): iterable
    {
        return $this->variables[$variable] ?? [];
    }
}
