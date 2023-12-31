<?php

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Webmozart\Assert\Assert;

class ActionConfigurationLoader
{
    /**
     * @var array<string, mixed>
     */
    private array $entityConfig;

    /**
     * @var array<string, mixed>
     */
    private array $actionConfig;

    public function __construct(
        private readonly RedirectionConfigurationLoader $redirectionConfigurationLoader,
        private readonly RouteConfigurationLoader $routeConfigurationLoader,
        private readonly ViewConfigurationLoader $viewConfigurationLoader,
    ) {
    }

    /**
     * @param class-string         $entityClass
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
        $this->entityConfig = $entityConfig;
        $this->actionConfig = $actionConfig;

        return new ActionConfiguration(
            $entityClass,
            $action,
            $this->getEntityName(),
            $this->getFormTypeClass(),
            $this->getHelperClass(),
            $this->getRedirectionConfiguration(),
            $this->getRouteConfiguration(),
            $this->getViewConfiguration(),
        );
    }

    private function getEntityName(): ?string
    {
        if (!array_key_exists('name', $this->entityConfig)) {
            return null;
        }

        $entityName = $this->entityConfig['name'];

        Assert::stringNotEmpty($entityName);

        return $entityName;
    }

    /**
     * @return null|class-string
     */
    private function getFormTypeClass(): ?string
    {
        if (!array_key_exists('formType', $this->actionConfig)) {
            return null;
        }

        Assert::string($this->actionConfig['formType']);
        Assert::classExists($this->actionConfig['formType']);

        return $this->actionConfig['formType'];
    }

    /**
     * @return null|class-string
     */
    private function getHelperClass(): ?string
    {
        if (!array_key_exists('helper', $this->actionConfig)) {
            return null;
        }

        Assert::string($this->actionConfig['helper']);
        Assert::classExists($this->actionConfig['helper']);

        return $this->actionConfig['helper'];
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    private function getRedirectionConfiguration(): ?RedirectionConfiguration
    {
        return $this->redirectionConfigurationLoader->load($this->actionConfig);
    }

    private function getRouteConfiguration(): RouteConfiguration
    {
        return $this->routeConfigurationLoader->load($this->actionConfig);
    }

    private function getViewConfiguration(): ?ViewConfiguration
    {
        return $this->viewConfigurationLoader->load($this->actionConfig);
    }
}
