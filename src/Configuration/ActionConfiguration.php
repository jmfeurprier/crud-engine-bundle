<?php

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;

readonly class ActionConfiguration
{
    /**
     * @param class-string      $entityClass
     * @param null|class-string $formTypeClass
     * @param null|class-string $helperClass
     */
    public function __construct(
        private string $entityClass,
        private string $action,
        private ?string $entityName,
        private ?string $formTypeClass,
        private ?string $helperClass,
        private ?RedirectionConfiguration $redirectionConfiguration,
        private RouteConfiguration $routeConfiguration,
        private ?ViewConfiguration $viewConfiguration,
    ) {
    }

    /**
     * @return class-string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getEntityName(): ?string
    {
        return $this->entityName;
    }

    /**
     * @return class-string
     *
     * @throws CrudEngineMissingConfigurationException
     */
    public function getFormTypeClass(): string
    {
        return $this->formTypeClass ?? $this->onMissingConfiguration('formType');
    }

    /**
     * @return null|class-string
     */
    public function getHelperClass(): ?string
    {
        return $this->helperClass;
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    public function getRedirectionConfiguration(): RedirectionConfiguration
    {
        return $this->redirectionConfiguration ?? $this->onMissingConfiguration('redirection');
    }

    public function getRouteConfiguration(): RouteConfiguration
    {
        return $this->routeConfiguration;
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    public function getViewConfiguration(): ViewConfiguration
    {
        return $this->viewConfiguration ?? $this->onMissingConfiguration('view');
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    private function onMissingConfiguration(string $configurationKey): never
    {
        throw new CrudEngineMissingConfigurationException(
            "Missing '{$configurationKey}' entity action configuration " .
            "for entity {$this->entityClass} and action '{$this->action}'."
        );
    }
}
