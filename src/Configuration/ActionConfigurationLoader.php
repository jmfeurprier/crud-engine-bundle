<?php

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Webmozart\Assert\Assert;

readonly class ActionConfigurationLoader
{
    public function __construct(
        private RedirectionConfigurationLoader $redirectionConfigurationLoader,
        private RouteConfigurationLoader $routeConfigurationLoader,
        private ViewConfigurationLoader $viewConfigurationLoader,
        private ActionConfigurationFallbacksResolver $fallbacksResolver,
    ) {
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $entityConfig
     * @param array<string, mixed> $actionConfig
     *
     * @throws CrudEngineMissingConfigurationException
     */
    public function load(
        string $entityClass,
        string $action,
        array $entityConfig,
        array $actionConfig,
    ): ActionConfiguration {
        return new ActionConfiguration(
            $entityClass,
            $action,
            $this->getEntityName($entityConfig),
            $this->getFormTypeClass($entityClass, $action, $actionConfig),
            $this->getHelperClass($entityClass, $action, $actionConfig),
            $this->getRedirectionConfiguration($entityClass, $action, $actionConfig),
            $this->getRouteConfiguration($entityClass, $action, $actionConfig),
            $this->getViewConfiguration($entityClass, $action, $actionConfig),
        );
    }

    /**
     * @param array<string, mixed> $entityConfig
     */
    private function getEntityName(
        array $entityConfig,
    ): ?string {
        if (!array_key_exists('name', $entityConfig)) {
            // @todo Generate name from entity class.

            return null;
        }

        $entityName = $entityConfig['name'];

        Assert::stringNotEmpty($entityName);

        return $entityName;
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @return null|class-string
     */
    private function getFormTypeClass(
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?string {
        if (!array_key_exists('formType', $actionConfig)) {
            return $this->fallbacksResolver->tryResolveFormTypeClass($entityClass, $action);
        }

        if (null === $actionConfig['formType']) {
            return null;
        }

        Assert::string($actionConfig['formType']);
        Assert::classExists($actionConfig['formType']);

        return $actionConfig['formType'];
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @return null|class-string
     */
    private function getHelperClass(
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?string {
        if (!array_key_exists('helper', $actionConfig)) {
            return $this->fallbacksResolver->tryResolveHelperClass($entityClass, $action);
        }

        if (null === $actionConfig['helper']) {
            return null;
        }

        Assert::string($actionConfig['helper']);
        Assert::classExists($actionConfig['helper']);

        return $actionConfig['helper'];
    }

    /**
     * @param class-string         $entityClass
     * @param array<string, mixed> $actionConfig
     *
     * @throws CrudEngineMissingConfigurationException
     */
    private function getRedirectionConfiguration(
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?RedirectionConfiguration {
        return $this->redirectionConfigurationLoader->load(
            $entityClass,
            $action,
            $actionConfig,
        );
    }

    /**
     * @param class-string         $entityClass
     * @param array<string, mixed> $actionConfig
     *
     * @throws CrudEngineMissingConfigurationException
     */
    private function getRouteConfiguration(
        string $entityClass,
        string $action,
        array $actionConfig,
    ): RouteConfiguration {
        return $this->routeConfigurationLoader->load(
            $entityClass,
            $action,
            $actionConfig,
        );
    }

    /**
     * @param class-string         $entityClass
     * @param array<string, mixed> $actionConfig
     *
     * @throws CrudEngineMissingConfigurationException
     */
    private function getViewConfiguration(
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?ViewConfiguration {
        return $this->viewConfigurationLoader->load(
            $entityClass,
            $action,
            $actionConfig,
        );
    }
}
