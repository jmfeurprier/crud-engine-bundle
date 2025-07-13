<?php

namespace Jmf\CrudEngine\Configuration\Entities\Action\Redirection;

readonly class ActionRedirectionConfiguration
{
    public function __construct(
        private string $route,
        private ActionRedirectionParameterCollection $parameters,
        private ?string $fragment = null,
    ) {
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getParameters(): ActionRedirectionParameterCollection
    {
        return $this->parameters;
    }

    public function getFragment(): ?string
    {
        return $this->fragment;
    }
}
