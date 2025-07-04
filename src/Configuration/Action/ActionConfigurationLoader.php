<?php

namespace Jmf\CrudEngine\Configuration\Action;

use Jmf\CrudEngine\Configuration\Action\FormType\FormTypeClassConfigurationLoader;
use Jmf\CrudEngine\Configuration\Action\Helper\HelperClassConfigurationLoader;
use Jmf\CrudEngine\Configuration\Action\Redirection\RedirectionConfiguration;
use Jmf\CrudEngine\Configuration\Action\Redirection\RedirectionConfigurationLoader;
use Jmf\CrudEngine\Configuration\Action\Route\RouteConfiguration;
use Jmf\CrudEngine\Configuration\Action\Route\RouteConfigurationLoader;
use Jmf\CrudEngine\Configuration\Action\View\ViewConfiguration;
use Jmf\CrudEngine\Configuration\Action\View\ViewConfigurationLoader;
use Jmf\CrudEngine\Configuration\EntityConfigurationFallbacksResolver;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Symfony\Component\Form\FormInterface;
use Webmozart\Assert\Assert;
use function Symfony\Component\String\u;

readonly class ActionConfigurationLoader
{
    public function __construct(
        private RedirectionConfigurationLoader $redirectionConfigurationLoader,
        private RouteConfigurationLoader $routeConfigurationLoader,
        private ViewConfigurationLoader $viewConfigurationLoader,
        private FormTypeClassConfigurationLoader $formTypeClassConfigurationLoader,
        private HelperClassConfigurationLoader $helperClassConfigurationLoader,
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
            $this->getEntityName($entityClass, $entityConfig),
            $this->getFormTypeClass($entityClass, $action, $actionConfig),
            $this->getHelperClass($entityClass, $action, $actionConfig),
            $this->getRedirectionConfiguration($entityClass, $action, $actionConfig),
            $this->getRouteConfiguration($entityClass, $action, $actionConfig),
            $this->getViewConfiguration($entityClass, $action, $actionConfig),
        );
    }

    /**
     * @param class-string         $entityClass
     * @param array<string, mixed> $entityConfig
     */
    private function getEntityName(
        string $entityClass,
        array $entityConfig,
    ): string {
        if (!array_key_exists('name', $entityConfig)) {
            return u($entityClass)->afterLast('\\')->snake()->toString();
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
     * @return null|class-string<FormInterface>
     */
    private function getFormTypeClass(
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?string {
        return $this->formTypeClassConfigurationLoader->load(
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
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?string {
        return $this->helperClassConfigurationLoader->load(
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
    ): ?RedirectionConfiguration {
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
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
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
