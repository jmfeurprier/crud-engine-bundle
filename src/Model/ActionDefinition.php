<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Model;

use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Registry\FormDefinition;
use Jmf\CrudEngine\Registry\RedirectionDefinition;
use Jmf\CrudEngine\Registry\RouteDefinition;
use Jmf\CrudEngine\Registry\ViewDefinition;

readonly class ActionDefinition
{
    /**
     * @param null|class-string $helperClass
     */
    public function __construct(
        private EntityAction $entityAction,
        private ?string $helperClass,
        private ?FormDefinition $formConfiguration,
        private ?RedirectionDefinition $redirectionConfiguration,
        private RouteDefinition $routeConfiguration,
        private ViewDefinition $viewConfiguration,
    ) {
    }

    public function getEntityAction(): EntityAction
    {
        return $this->entityAction;
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
    public function getFormConfiguration(): FormDefinition
    {
        return $this->formConfiguration
            ??
            throw new CrudEngineMissingConfigurationException(
                $this->entityAction->getEntityClass(),
                $this->entityAction->getAction(),
                'form',
            );
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    public function getRedirectionConfiguration(): RedirectionDefinition
    {
        return $this->redirectionConfiguration
            ??
            throw new CrudEngineMissingConfigurationException(
                $this->entityAction->getEntityClass(),
                $this->entityAction->getAction(),
                'redirection',
            );
    }

    public function getRouteConfiguration(): RouteDefinition
    {
        return $this->routeConfiguration;
    }

    public function getViewConfiguration(): ViewDefinition
    {
        return $this->viewConfiguration;
    }
}
