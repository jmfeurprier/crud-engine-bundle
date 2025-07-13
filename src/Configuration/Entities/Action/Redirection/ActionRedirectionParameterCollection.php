<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\Redirection;

use Webmozart\Assert\Assert;

readonly class ActionRedirectionParameterCollection
{
    public static function createDefault(): self
    {
        return new self([]);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function __construct(
        private array $parameters,
    ) {
        Assert::isMap($this->parameters);
        Assert::allString($this->parameters);
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->parameters;
    }

    public function tryGet(
        string $key,
        string $default,
    ): string {
        return $this->parameters[$key] ?? $default;
    }
}
