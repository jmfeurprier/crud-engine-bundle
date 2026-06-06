<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action;

use Jmf\CrudEngine\Configuration\Entities\Action\Form\ActionFormConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Redirection\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\ActionRouteConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;

readonly class ActionConfiguration
{
    /**
     * @param class-string      $entityClass
     * @param non-empty-string  $action
     * @param null|class-string $helperClass
     */
    public function __construct(
        private string $entityClass,
        private string $action,
        private ?string $helperClass,
        private ActionFormConfiguration $formConfiguration,
        private ?ActionRedirectionConfiguration $redirectionConfiguration,
        private ActionRouteConfiguration $routeConfiguration,
        private ActionViewConfiguration $viewConfiguration,
    ) {
    }

    /**
     * @return class-string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @return non-empty-string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return null|class-string
     */
    public function getHelperClass(): ?string
    {
        return $this->helperClass;
    }

    public function getFormConfiguration(): ActionFormConfiguration
    {
        return $this->formConfiguration;
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    public function getRedirectionConfiguration(): ActionRedirectionConfiguration
    {
        return $this->redirectionConfiguration ?? $this->onMissingConfiguration('redirection');
    }

    public function getRouteConfiguration(): ActionRouteConfiguration
    {
        return $this->routeConfiguration;
    }

    public function getViewConfiguration(): ActionViewConfiguration
    {
        return $this->viewConfiguration;
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    private function onMissingConfiguration(string $configurationKey): never
    {
        throw new CrudEngineMissingConfigurationException(
            $this->entityClass,
            $this->action,
            $configurationKey,
        );
    }
}
