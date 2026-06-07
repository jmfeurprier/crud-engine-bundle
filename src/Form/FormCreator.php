<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Form;

use Jmf\CrudEngine\Exception\CrudEngineFormCreationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Model\ActionDefinition;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Throwable;

readonly class FormCreator
{
    public function __construct(
        private FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * @throws CrudEngineFormCreationException
     * @throws CrudEngineMissingConfigurationException
     */
    public function create(
        ActionDefinition $actionDefinition,
        object $entity,
    ): FormInterface {
        $formConfiguration = $actionDefinition->getFormConfiguration();
        $formTypeClass     = $formConfiguration->getFormTypeClass();

        if (null !== $formTypeClass) {
            return $this->createForm($actionDefinition, $formTypeClass, $entity);
        }

        if (FormFallbackMode::FAIL === $formConfiguration->getFormFallbackMode()) {
            throw new CrudEngineMissingConfigurationException(
                $actionDefinition->getEntityAction()->getEntityClass(),
                $actionDefinition->getEntityAction()->getAction(),
                'form.type',
            );
        }

        return $this->createForm(
            $actionDefinition,
            CrudEngineEntityType::class,
            $entity,
            [
                'entity_action'             => $actionDefinition->getEntityAction(),
                'suggested_form_type_class' => $formConfiguration->getSuggestedFormTypeClass(),
            ],
        );
    }

    /**
     * @param class-string<FormTypeInterface> $formTypeClass
     * @param array<string, mixed>            $options
     *
     * @throws CrudEngineFormCreationException
     */
    private function createForm(
        ActionDefinition $actionDefinition,
        string $formTypeClass,
        object $entity,
        array $options = [],
    ): FormInterface {
        try {
            return $this->formFactory->create($formTypeClass, $entity, $options);
        } catch (Throwable $e) {
            throw new CrudEngineFormCreationException(
                entityAction: $actionDefinition->getEntityAction(),
                previous:     $e,
            );
        }
    }
}
