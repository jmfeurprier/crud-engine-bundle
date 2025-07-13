<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action;

use Jmf\CrudEngine\Configuration\Entities\Action\Redirection\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\RouteConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Symfony\Component\Form\FormTypeInterface;

readonly class ActionConfiguration
{
    /**
     * @param class-string                         $entityClass
     * @param null|class-string<FormTypeInterface> $formTypeClass
     * @param null|class-string                    $helperClass
     */
    public function __construct(
        private string $entityClass,
        private string $action,
        private ?string $formTypeClass,
        private ?string $helperClass,
        private ?ActionRedirectionConfiguration $redirectionConfiguration,
        private RouteConfiguration $routeConfiguration,
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

    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return class-string<FormTypeInterface>
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
    public function getRedirectionConfiguration(): ActionRedirectionConfiguration
    {
        return $this->redirectionConfiguration ?? $this->onMissingConfiguration('redirection');
    }

    public function getRouteConfiguration(): RouteConfiguration
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
