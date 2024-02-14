<?php

namespace Jmf\CrudEngine\Configuration;

use RuntimeException;
use Webmozart\Assert\Assert;

class RouteConfigurationLoader
{
    /**
     * @var array<string, mixed>
     */
    private array $routeConfig;

    /**
     * @param array<string, mixed> $actionConfig
     */
    public function load(array $actionConfig): RouteConfiguration
    {
        Assert::keyExists($actionConfig, 'route');

        Assert::isMap($actionConfig['route']);

        $this->routeConfig = $actionConfig['route'];

        return new RouteConfiguration(
            $this->getName(),
            $this->getPath(),
            $this->getParameters(),
            $this->getRequirements(),
        );
    }

    private function getName(): ?string
    {
        if (!array_key_exists('name', $this->routeConfig)) {
            return null;
        }

        Assert::string($this->routeConfig['name']);

        return $this->routeConfig['name'];
    }

    private function getPath(): string
    {
        if (!array_key_exists('path', $this->routeConfig)) {
            throw new RuntimeException();
        }

        Assert::string($this->routeConfig['path']);

        return $this->routeConfig['path'];
    }

    private function getParameters(): KeyStringCollection
    {
        if (!array_key_exists('parameters', $this->routeConfig)) {
            return KeyStringCollection::createEmpty();
        }

        Assert::isArray($this->routeConfig['parameters']);

        return new KeyStringCollection(
            $this->routeConfig['parameters'],
        );
    }

    private function getRequirements(): KeyStringCollection
    {
        if (!array_key_exists('requirements', $this->routeConfig)) {
            return KeyStringCollection::createEmpty();
        }

        Assert::isArray($this->routeConfig['requirements']);

        return new KeyStringCollection(
            $this->routeConfig['requirements'],
        );
    }
}
