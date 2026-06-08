<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller;

use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\CreateActionHelperInterface;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperNotAnObjectException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperRetrievalException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperTypeMismatchException;
use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerTypeMismatchException;
use Jmf\CrudEngine\Exception\CrudEngineFormCreationException;
use Jmf\CrudEngine\Exception\CrudEngineFormRequestHandlingException;
use Jmf\CrudEngine\Exception\CrudEngineFormViewCreationException;
use Jmf\CrudEngine\Exception\CrudEngineInstantiationFailureException;
use Jmf\CrudEngine\Exception\CrudEngineMissingViewException;
use Jmf\CrudEngine\Exception\CrudEnginePersistenceException;
use Jmf\CrudEngine\Exception\CrudEngineRedirectionException;
use Jmf\CrudEngine\Exception\CrudEngineRedirectionParameterRenderingException;
use Jmf\CrudEngine\Exception\CrudEngineViewRenderingException;
use Jmf\CrudEngine\Form\FormCreator;
use Jmf\CrudEngine\Model\CrudAction;
use Jmf\CrudEngine\Persistence\EntityManagerResolver;
use Jmf\CrudEngine\Redirection\RedirectionGenerator;
use Jmf\CrudEngine\Registry\ActionDefinitionRegistryInterface;
use Jmf\CrudEngine\View\ViewRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Throwable;

/**
 * @template E of object
 */
#[AsController]
readonly class CreateAction
{
    /**
     * @param CreateActionHelperInterface<E> $defaultActionHelper
     */
    public function __construct(
        private ActionDefinitionRegistryInterface $actionDefinitionRegistry,
        private ActionHelperResolver $actionHelperResolver,
        private CreateActionHelperInterface $defaultActionHelper,
        private FormCreator $formCreator,
        private EntityManagerResolver $objectManagerResolver,
        private RedirectionGenerator $redirectionGenerator,
        private ViewRenderer $viewRenderer,
    ) {
    }

    /**
     * @param class-string<E> $entityClass
     *
     * @throws CrudEngineActionHelperNotAnObjectException
     * @throws CrudEngineActionHelperNotFoundException
     * @throws CrudEngineActionHelperRetrievalException
     * @throws CrudEngineActionHelperTypeMismatchException
     * @throws CrudEngineConfigurationException
     * @throws CrudEngineEntityManagerNotFoundException
     * @throws CrudEngineEntityManagerTypeMismatchException
     * @throws CrudEngineFormCreationException
     * @throws CrudEngineFormRequestHandlingException
     * @throws CrudEngineFormViewCreationException
     * @throws CrudEngineInstantiationFailureException
     * @throws CrudEngineMissingViewException
     * @throws CrudEnginePersistenceException
     * @throws CrudEngineRedirectionException
     * @throws CrudEngineRedirectionParameterRenderingException
     * @throws CrudEngineViewRenderingException
     */
    public function __invoke(
        Request $request,
        string $entityClass,
    ): Response {
        $actionDefinition = $this->actionDefinitionRegistry->get(
            $entityClass,
            CrudAction::Create,
        );

        $actionHelper = $this->actionHelperResolver->resolve(
            CreateActionHelperInterface::class,
            $actionDefinition,
            $this->defaultActionHelper,
        );

        $entity = $actionHelper->createEntity(
            $request,
            $entityClass,
        );

        $form = $this->formCreator->create(
            $actionDefinition,
            $entity,
        );

        try {
            $form->handleRequest($request);
        } catch (Throwable $e) {
            throw new CrudEngineFormRequestHandlingException(
                entityAction: $actionDefinition->getEntityAction(),
                previous:     $e,
            );
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $actionHelper->hookBeforePersist(
                $request,
                $entity,
                $form,
            );

            $actionHelper->persist(
                $request,
                $entity,
                $form,
                $this->objectManagerResolver->resolve($entityClass),
            );

            $actionHelper->hookAfterPersist(
                $request,
                $entity,
                $form,
            );

            return $this->redirectionGenerator->generate($actionDefinition, $entity);
        }

        try {
            $formView = $form->createView();
        } catch (Throwable $e) {
            throw new CrudEngineFormViewCreationException(
                entityAction: $actionDefinition->getEntityAction(),
                previous:     $e,
            );
        }

        return $this->viewRenderer->render(
            $actionDefinition,
            $actionHelper->getViewVariables($request, $entity),
            [
                'entity' => $entity,
                'form'   => $formView,
            ],
        );
    }
}
