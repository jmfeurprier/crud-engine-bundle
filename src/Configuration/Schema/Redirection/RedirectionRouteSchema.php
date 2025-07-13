<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Schema\Redirection;

use Webmozart\Assert\Assert;

readonly class RedirectionRouteSchema
{
    /**
     * @param non-empty-string                $route
     * @param array<string, non-empty-string> $parameters
     */
    public function __construct(
        private string $route,
        private array $parameters,
    ) {
        Assert::stringNotEmpty($route);
        Assert::isMap($parameters);
        Assert::allStringNotEmpty($parameters);
    }

    /**
     * @return non-empty-string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @return array<string, non-empty-string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
