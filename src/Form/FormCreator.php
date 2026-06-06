<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Form;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Form\FormFallbackMode;
use Jmf\CrudEngine\Exception\CrudEngineFormCreationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
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
                'action'                    => $actionConfiguration->getAction(),
                'entity_class'              => $actionConfiguration->getEntityClass(),
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
        ActionConfiguration $actionConfiguration,
        string $formTypeClass,
        object $entity,
        array $options = [],
    ): FormInterface {
        try {
            return $this->formFactory->create($formTypeClass, $entity, $options);
        } catch (Throwable $e) {
            throw new CrudEngineFormCreationException(
                entityClass: $actionConfiguration->getEntityClass(),
                action:      $actionConfiguration->getAction(),
                previous:    $e,
            );
        }
    }
}
