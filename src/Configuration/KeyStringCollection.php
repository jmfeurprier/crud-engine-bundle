<?php

namespace Jmf\CrudEngine\Configuration;

use Webmozart\Assert\Assert;

readonly class KeyStringCollection
{
    public static function createEmpty(): self
    {
        return new self([]);
    }

    /**
     * @param array<string, string> $values
     */
    public function __construct(
        private array $values,
    ) {
        Assert::isMap($this->values);
        Assert::allString($this->values);
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->values;
    }

    public function tryGet(string $key, string $default): string
    {
        return $this->values[$key] ?? $default;
    }
}
