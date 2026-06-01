<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\Redirection;

readonly class ActionRedirectionConfiguration
{
    /**
     * @param array<string, string> $parameters
     */
    public function __construct(
        private string $route,
        private array $parameters,
        private ?string $fragment = null,
    ) {
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getFragment(): ?string
    {
        return $this->fragment;
    }
}
