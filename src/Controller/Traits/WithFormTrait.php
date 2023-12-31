<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

trait WithFormTrait
{
    private FormFactoryInterface $formFactory;

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    private function getForm(
        ActionConfiguration $actionConfiguration,
        object $entity,
    ): FormInterface {
        return $this->formFactory->create($this->getFormTypeClass($actionConfiguration), $entity);
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    private function getFormTypeClass(ActionConfiguration $actionConfiguration): string
    {
        return $actionConfiguration->getFormTypeClass();
    }
}
