<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller\Dependencies;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Form\FormFallbackMode;
use Jmf\CrudEngine\Exception\CrudEngineFormException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Form\CrudEngineEntityType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Throwable;

readonly class FormCreator
{
    public function __construct(
        private FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * @throws CrudEngineFormException
     * @throws CrudEngineMissingConfigurationException
     */
    public function create(
        ActionConfiguration $actionConfiguration,
        object $entity,
    ): FormInterface {
        $formConfiguration = $actionConfiguration->getFormConfiguration();
        $formTypeClass     = $formConfiguration->getFormTypeClass();

        if (null !== $formTypeClass) {
            return $this->createForm($actionConfiguration, $formTypeClass, $entity);
        }

        if (FormFallbackMode::FAIL === $formConfiguration->getFormFallbackMode()) {
            throw new CrudEngineMissingConfigurationException(
                $actionConfiguration->getEntityClass(),
                $actionConfiguration->getAction(),
                'formType',
            );
        }

        return $this->createForm(
            $actionConfiguration,
            CrudEngineEntityType::class,
            $entity,
            [
                'entity_class'              => $actionConfiguration->getEntityClass(),
                'suggested_form_type_class' => $formConfiguration->getSuggestedFormTypeClass(),
            ],
        );
    }

    /**
     * @param class-string<\Symfony\Component\Form\FormTypeInterface> $formTypeClass
     * @param array<string, mixed>                                    $options
     *
     * @throws CrudEngineFormException
     */
    private function createForm(
        ActionConfiguration $actionConfiguration,
        string $formTypeClass,
        object $entity,
        array $options = [],
    ): FormInterface {
        try {
            return $this->formFactory->create($formTypeClass, $entity, $options);
        } catch (Throwable $e) {
            throw new CrudEngineFormException($actionConfiguration->getEntityClass(), $e);
        }
    }
}
