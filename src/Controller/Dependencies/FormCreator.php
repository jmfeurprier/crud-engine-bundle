<?php

namespace Jmf\CrudEngine\Controller\Dependencies;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

readonly class FormCreator
{
    public function __construct(
        private FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    public function create(
        ActionConfiguration $actionConfiguration,
        object $entity,
    ): FormInterface {
        return $this->formFactory->create($actionConfiguration->getFormTypeClass(), $entity);
    }
}
