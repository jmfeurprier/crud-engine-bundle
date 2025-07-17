<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\Route\Requirements;

use Webmozart\Assert\Assert;

readonly class ActionRouteRequirementCollection
{
    public static function createDefault(): self
    {
        return new self([]);
    }

    /**
     * @param array<non-empty-string, non-empty-string> $values
     */
    public function __construct(
        private array $values,
    ) {
        Assert::isMap($this->values);
        Assert::allString($this->values);
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function all(): array
    {
        return $this->values;
    }

    /**
     * @param non-empty-string $key
     */
    public function tryGet(
        string $key,
        string $default,
    ): string {
        return $this->values[$key] ?? $default;
    }
}
