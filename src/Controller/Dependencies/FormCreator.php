<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller\Dependencies;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Form\FormFallbackMode;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Form\CrudEngineEntityType;
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
        $formTypeClass = $actionConfiguration->getFormConfiguration()->getFormTypeClass();

        if (null !== $formTypeClass) {
            return $this->formFactory->create($formTypeClass, $entity);
        }

        if (FormFallbackMode::FAIL === $actionConfiguration->getFormConfiguration()->getFormFallbackMode()) {
            throw new CrudEngineMissingConfigurationException(
                $actionConfiguration->getEntityClass(),
                $actionConfiguration->getAction(),
                'formType',
            );
        }

        return $this->formFactory->create(
            CrudEngineEntityType::class,
            $entity,
            [
                'entity_class' => $actionConfiguration->getEntityClass(),
            ],
        );
    }
}
