<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Definition;

use Jmf\CrudEngine\Definition\FormDefinition;
use Jmf\CrudEngine\Definition\RedirectionDefinition;
use Jmf\CrudEngine\Definition\RouteDefinition;
use Jmf\CrudEngine\Definition\ViewDefinition;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Model\EntityAction;

readonly class ActionDefinition
{
    /**
     * @param null|class-string $helperClass
     */
    public function __construct(
        private EntityAction $entityAction,
        private ?string $helperClass,
        private ?FormDefinition $formDefinition,
        private ?RedirectionDefinition $redirectionDefinition,
        private RouteDefinition $routeDefinition,
        private ViewDefinition $viewDefinition,
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
    public function getFormDefinition(): FormDefinition
    {
        return $this->formDefinition
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
    public function getRedirectionDefinition(): RedirectionDefinition
    {
        return $this->redirectionDefinition
            ??
            throw new CrudEngineMissingConfigurationException(
                $this->entityAction->getEntityClass(),
                $this->entityAction->getAction(),
                'redirection',
            );
    }

    public function getRouteDefinition(): RouteDefinition
    {
        return $this->routeDefinition;
    }

    public function getViewDefinition(): ViewDefinition
    {
        return $this->viewDefinition;
    }
}
