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
     * @param array<non-empty-string, non-empty-string> $requirements
     */
    public function __construct(
        private array $requirements,
    ) {
        Assert::isMap($this->requirements);
        Assert::allString($this->requirements);
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function all(): array
    {
        return $this->requirements;
    }

    /**
     * @param non-empty-string $key
     */
    public function tryGet(
        string $key,
        string $default,
    ): string {
        return $this->requirements[$key] ?? $default;
    }
}
