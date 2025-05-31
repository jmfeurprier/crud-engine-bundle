<?php

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Webmozart\Assert\Assert;

readonly class RouteConfigurationLoader
{
    /**
     * @param class-string         $entityClass
     * @param array<string, mixed> $actionConfig
     *
     * @throws CrudEngineMissingConfigurationException
     */
    public function load(
        string $entityClass,
        string $action,
        array $actionConfig,
    ): RouteConfiguration {
        if (!array_key_exists('route', $actionConfig)) {
            throw new CrudEngineMissingConfigurationException(
                $entityClass,
                $action,
                'route',
            );
        }

        Assert::isMap($actionConfig['route']);

        $routeConfig = $actionConfig['route'];

        return new RouteConfiguration(
            $this->getName($routeConfig),
            $this->getPath($entityClass, $action, $routeConfig),
            $this->getParameters($routeConfig),
            $this->getRequirements($routeConfig),
        );
    }

    /**
     * @param array<string, mixed> $routeConfig
     *
     * @return null|non-empty-string
     */
    private function getName(array $routeConfig): ?string
    {
        if (!array_key_exists('name', $routeConfig)) {
            return null;
        }

        Assert::stringNotEmpty($routeConfig['name']);

        return $routeConfig['name'];
    }

    /**
     * @param class-string         $entityClass
     * @param array<string, mixed> $routeConfig
     *
     * @throws CrudEngineMissingConfigurationException
     */
    private function getPath(
        string $entityClass,
        string $action,
        array $routeConfig,
    ): string {
        if (!array_key_exists('path', $routeConfig)) {
            throw new CrudEngineMissingConfigurationException(
                $entityClass,
                $action,
                'route.path',
            );
        }

        Assert::stringNotEmpty($routeConfig['path']);

        return $routeConfig['path'];
    }

    /**
     * @param array<string, mixed> $routeConfig
     */
    private function getParameters(array $routeConfig): KeyStringCollection
    {
        if (!array_key_exists('parameters', $routeConfig)) {
            return KeyStringCollection::createEmpty();
        }

        Assert::isArray($routeConfig['parameters']);

        return new KeyStringCollection(
            $routeConfig['parameters'],
        );
    }

    /**
     * @param array<string, mixed> $routeConfig
     */
    private function getRequirements(array $routeConfig): KeyStringCollection
    {
        if (!array_key_exists('requirements', $routeConfig)) {
            return KeyStringCollection::createEmpty();
        }

        Assert::isArray($routeConfig['requirements']);

        return new KeyStringCollection(
            $routeConfig['requirements'],
        );
    }
}
