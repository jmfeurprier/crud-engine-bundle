<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

trait WithFormTrait
{
    private FormFactoryInterface $formFactory;

    /**
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    protected function getForm(
        array $actionProperties,
        object $entity
    ): FormInterface {
        return $this->formFactory->create($this->getFormTypeClass($actionProperties), $entity);
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     * @throws CrudEngineInvalidConfigurationException
     */
    private function getFormTypeClass(array $actionProperties): string
    {
        if (!array_key_exists('formTypeClass', $actionProperties)) {
            throw new CrudEngineMissingConfigurationException("Missing 'formTypeClass' configuration.");
        }

        $formTypeClass = $actionProperties['formTypeClass'];

        if (!is_string($formTypeClass)) {
            throw new CrudEngineInvalidConfigurationException("Invalid 'formTypeClass' configuration type.");
        }

        return $formTypeClass;
    }
}
