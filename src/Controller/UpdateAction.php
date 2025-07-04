<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Controller\Dependencies\FormCreator;
use Jmf\CrudEngine\Controller\Dependencies\Redirector;
use Jmf\CrudEngine\Controller\Dependencies\ViewRenderer;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\UpdateActionHelperInterface;
use Jmf\CrudEngine\Controller\Traits\WithActionHelperTrait;
use Jmf\CrudEngine\Controller\Traits\WithEntityManagerTrait;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineRedirectionParameterRenderingException;
use Jmf\CrudEngine\Exception\CrudEngineViewRenderingException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @template E of object
 */
#[AsController]
readonly class UpdateAction
{
    /**
     * @use WithActionHelperTrait<UpdateActionHelperInterface<E>>
     */
    use WithActionHelperTrait;
    use WithEntityManagerTrait;

    /**
     * @psalm-param UpdateActionHelperInterface<E> $defaultActionHelper
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        ActionHelperResolver $actionHelperResolver,
        private ActionConfigurationRepositoryInterface $actionConfigurationRepository,
        private Redirector $redirector,
        private ViewRenderer $viewRenderer,
        private FormCreator $formCreator,
        private UpdateActionHelperInterface $defaultActionHelper,
    ) {
        $this->managerRegistry      = $managerRegistry;
        $this->actionHelperResolver = $actionHelperResolver;
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
        $actionHelper        = $this->getActionHelper(
            UpdateActionHelperInterface::class,
            $actionConfiguration,
            $this->defaultActionHelper,
        );

        $entity = $this->getEntity($entityClass, $id);
        $form   = $this->formCreator->create($actionConfiguration, $entity);

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
                $this->getEntityManager($entityClass),
            );

            $actionHelper->hookAfterPersist(
                $request,
                $entity,
                $form,
            );

            return $this->redirector->redirect($actionConfiguration, $entity);
        }

        return $this->viewRenderer->render(
            $actionConfiguration,
            $this->getViewContext(
                $request,
                $actionHelper,
                $entity,
                [
                    'entity' => $entity,
                    'form'   => $form->createView(),
                ],
            ),
        );
    }

    /**
     * @param class-string<E> $entityClass
     *
     * @psalm-return E
     *
     * @throws NotFoundHttpException
     */
    private function getEntity(
        string $entityClass,
        string $id,
    ): object {
        $entity = $this->getRepository($entityClass)->find($id);

        return $entity ?? throw new NotFoundHttpException();
    }

    /**
     * @psalm-param UpdateActionHelperInterface<E> $actionHelper
     * @psalm-param E                              $entity
     * @psalm-param array<string, mixed>           $defaults
     *
     * @return array<string, mixed>
     */
    private function getViewContext(
        Request $request,
        UpdateActionHelperInterface $actionHelper,
        object $entity,
        array $defaults,
    ): array {
        return array_merge(
            $actionHelper->getViewVariables($request, $entity),
            $defaults,
        );
    }
}
