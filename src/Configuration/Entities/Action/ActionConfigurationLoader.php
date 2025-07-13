<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action;

use Jmf\CrudEngine\Configuration\Entities\Action\FormType\FormTypeClassResolver;
use Jmf\CrudEngine\Configuration\Entities\Action\Helper\HelperClassResolver;
use Jmf\CrudEngine\Configuration\Entities\Action\Redirection\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Redirection\ActionRedirectionConfigurationLoader;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\RouteConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\RouteConfigurationLoader;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfigurationLoader;
use Jmf\CrudEngine\Configuration\Schema\Schema;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Symfony\Component\Form\FormTypeInterface;

readonly class ActionConfigurationLoader
{
    public function __construct(
        private ActionRedirectionConfigurationLoader $redirectionConfigurationLoader,
        private RouteConfigurationLoader $routeConfigurationLoader,
        private ActionViewConfigurationLoader $viewConfigurationLoader,
        private FormTypeClassResolver $formTypeClassResolver,
        private HelperClassResolver $helperClassResolver,
        private SchemaValueExpander $schemaValueExpander,
    ) {
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @throws CrudEngineConfigurationException
     */
    public function load(
        Schema $schema,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ActionConfiguration {
        // @todo Pass $keys to sub-loaders.
        $keys = $schema->getKeySchema()->expand(
            $this->schemaValueExpander,
            [
                'entityClass' => $entityClass,
                'action'      => $action,
            ],
        );

        return new ActionConfiguration(
            $entityClass,
            $action,
            $this->getFormTypeClass($schema, $entityClass, $action, $actionConfig),
            $this->getHelperClass($schema, $entityClass, $action, $actionConfig),
            $this->getRedirectionConfiguration($entityClass, $action, $actionConfig),
            $this->getRouteConfiguration($schema, $entityClass, $action, $actionConfig),
            $this->getViewConfiguration($schema, $entityClass, $action, $actionConfig),
        );
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @return null|class-string<FormTypeInterface>
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function getFormTypeClass(
        Schema $schema,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?string {
        return $this->formTypeClassResolver->resolve(
            $schema->getFormTypeSchema(),
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
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function getHelperClass(
        Schema $schema,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?string {
        return $this->helperClassResolver->resolve(
            $schema->getHelperSchema(),
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
        Schema $schema,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): RouteConfiguration {
        return $this->routeConfigurationLoader->load(
            $schema->getRouteSchema(),
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
        Schema $schema,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ActionViewConfiguration {
        return $this->viewConfigurationLoader->load(
            $schema->getViewSchema(),
            $entityClass,
            $action,
            $actionConfig,
        );
    }
}
