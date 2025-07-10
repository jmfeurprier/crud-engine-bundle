<?php

namespace Jmf\CrudEngine\Configuration\Entities\Action;

use Jmf\CrudEngine\Configuration\Entities\Action\FormType\FormTypeClassResolver;
use Jmf\CrudEngine\Configuration\Entities\Action\Helper\HelperClassResolver;
use Jmf\CrudEngine\Configuration\Entities\Action\Redirection\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Redirection\ActionRedirectionConfigurationLoader;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\RouteConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\RouteConfigurationLoader;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfigurationLoader;
use Jmf\CrudEngine\Configuration\Schema\SchemaConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Symfony\Component\Form\FormTypeInterface;

readonly class ActionConfigurationLoader
{
    public function __construct(
        private ActionRedirectionConfigurationLoader $redirectionConfigurationLoader,
        private RouteConfigurationLoader $routeConfigurationLoader,
        private ActionViewConfigurationLoader $viewConfigurationLoader,
        private FormTypeClassResolver $formTypeClassConfigurationLoader,
        private HelperClassResolver $helperClassConfigurationLoader,
    ) {
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    public function load(
        SchemaConfiguration $schemaConfiguration,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ActionConfiguration {
        return new ActionConfiguration(
            $entityClass,
            $action,
            $this->getFormTypeClass($schemaConfiguration, $entityClass, $action, $actionConfig),
            $this->getHelperClass($schemaConfiguration, $entityClass, $action, $actionConfig),
            $this->getRedirectionConfiguration($entityClass, $action, $actionConfig),
            $this->getRouteConfiguration($schemaConfiguration, $entityClass, $action, $actionConfig),
            $this->getViewConfiguration($schemaConfiguration, $entityClass, $action, $actionConfig),
        );
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @return null|class-string<FormTypeInterface>
     */
    private function getFormTypeClass(
        SchemaConfiguration $schemaConfiguration,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?string {
        return $this->formTypeClassConfigurationLoader->resolve(
            $schemaConfiguration,
            $entityClass,
            $action,
            $actionConfig,
        );
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @return null|class-string
     */
    private function getHelperClass(
        SchemaConfiguration $schemaConfiguration,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?string {
        return $this->helperClassConfigurationLoader->resolve(
            $schemaConfiguration,
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
    private function getRedirectionConfiguration(
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?ActionRedirectionConfiguration {
        return $this->redirectionConfigurationLoader->load(
            $entityClass,
            $action,
            $actionConfig,
        );
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    private function getRouteConfiguration(
        SchemaConfiguration $schemaConfiguration,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): RouteConfiguration {
        return $this->routeConfigurationLoader->load(
            $schemaConfiguration,
            $entityClass,
            $action,
            $actionConfig,
        );
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function getViewConfiguration(
        SchemaConfiguration $schemaConfiguration,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ActionViewConfiguration {
        return $this->viewConfigurationLoader->load(
            $schemaConfiguration,
            $entityClass,
            $action,
            $actionConfig,
        );
    }
}
