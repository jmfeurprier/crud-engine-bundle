<?php

namespace Jmf\CrudEngine\Configuration;

readonly class RouteConfiguration
{
    public function __construct(
        private ?string $name,
        private string $path,
        private KeyStringCollection $parameters,
    ) {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getParameters(): KeyStringCollection
    {
        return $this->parameters;
    }
}
