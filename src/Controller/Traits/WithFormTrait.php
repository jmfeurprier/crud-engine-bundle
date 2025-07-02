<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

trait WithFormTrait
{
    private readonly FormFactoryInterface $formFactory;

    /**
     * @template TE of object
     *
     * @psalm-param TE $entity
     *
     * @return FormInterface<TE>
     *
     * @throws CrudEngineMissingConfigurationException
     */
    private function getForm(
        ActionConfiguration $actionConfiguration,
        object $entity,
    ): FormInterface {
        return $this->formFactory->create($actionConfiguration->getFormTypeClass(), $entity);
    }
}
