<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action;

use Jmf\CrudEngine\Configuration\Entities\Action\Form\ActionFormConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Redirection\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\ActionRouteConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Model\EntityAction;

readonly class ActionConfiguration
{
    /**
     * @param null|class-string $helperClass
     */
    public function __construct(
        private EntityAction $entityAction,
        private ?string $helperClass,
        private ActionFormConfiguration $formConfiguration,
        private ?ActionRedirectionConfiguration $redirectionConfiguration,
        private ActionRouteConfiguration $routeConfiguration,
        private ActionViewConfiguration $viewConfiguration,
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
            $this->entityAction->getEntityClass(),
            $this->entityAction->getAction(),
            $configurationKey,
        );
    }
}
