<?php

namespace Jmf\CrudEngine\Controller;

use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Controller\Dependencies\EntityFinder;
use Jmf\CrudEngine\Controller\Dependencies\EntityManagerResolver;
use Jmf\CrudEngine\Controller\Dependencies\FormCreator;
use Jmf\CrudEngine\Controller\Dependencies\RedirectionGenerator;
use Jmf\CrudEngine\Controller\Dependencies\ViewRenderer;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\UpdateActionHelperInterface;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineRedirectionParameterRenderingException;
use Jmf\CrudEngine\Exception\CrudEngineViewRenderingException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * @template E of object
 */
#[AsController]
readonly class UpdateAction
{
    /**
     * @psalm-param UpdateActionHelperInterface<E> $defaultActionHelper
     */
    public function __construct(
        private EntityFinder $entityFinder,
        private EntityManagerResolver $entityManagerResolver,
        private ActionHelperResolver $actionHelperResolver,
        private ActionConfigurationRepositoryInterface $actionConfigurationRepository,
        private RedirectionGenerator $redirectionGenerator,
        private ViewRenderer $viewRenderer,
        private FormCreator $formCreator,
        private UpdateActionHelperInterface $defaultActionHelper,
    ) {
    }

    /**
     * @param class-string<E> $entityClass
     *
     * @throws CrudEngineEntityManagerNotFoundException
     * @throws CrudEngineInvalidActionHelperException
     * @throws CrudEngineMissingConfigurationException
     * @throws CrudEngineRedirectionParameterRenderingException
     * @throws CrudEngineViewRenderingException
     */
    public function __invoke(
        Request $request,
        string $entityClass,
        string $id,
    ): Response {
        $actionConfiguration = $this->actionConfigurationRepository->get($entityClass, 'update');
        $actionHelper        = $this->actionHelperResolver->resolve(
            UpdateActionHelperInterface::class,
            $actionConfiguration,
            $this->defaultActionHelper,
        );

        $entity = $this->entityFinder->find($entityClass, $id);

        $form = $this->formCreator->create($actionConfiguration, $entity);
        $form->handleRequest($request);

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
                $this->entityManagerResolver->resolve($entityClass),
            );

            $actionHelper->hookAfterPersist(
                $request,
                $entity,
                $form,
            );

            return $this->redirectionGenerator->generate($actionConfiguration, $entity);
        }

        return $this->viewRenderer->render(
            $actionConfiguration,
            $actionHelper->getViewVariables($request, $entity),
            [
                'entity' => $entity,
                'form'   => $form->createView(),
            ],
        );
    }
}
