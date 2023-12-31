<?php

namespace Jmf\CrudEngine\Configuration;

readonly class RedirectionConfiguration
{
    public function __construct(
        private string $route,
        private KeyStringCollection $parameters,
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
}
