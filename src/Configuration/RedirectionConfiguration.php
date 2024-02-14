<?php

namespace Jmf\CrudEngine\Configuration;

readonly class RedirectionConfiguration
{
    public function __construct(
        private string $route,
        private KeyStringCollection $parameters,
        private ?string $fragment = null,
    ) {
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getParameters(): KeyStringCollection
    {
        return $this->parameters;
    }

    public function getFragment(): ?string
    {
        return $this->fragment;
    }
}
