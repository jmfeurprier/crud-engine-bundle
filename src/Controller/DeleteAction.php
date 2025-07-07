<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Controller\Dependencies\Redirector;
use Jmf\CrudEngine\Controller\Dependencies\ViewRenderer;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\DeleteActionHelperInterface;
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
use Throwable;

/**
 * @template E of object
 */
#[AsController]
readonly class DeleteAction
{
    use WithEntityManagerTrait;

    /**
     * @param DeleteActionHelperInterface<E> $defaultActionHelper
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        private ActionHelperResolver $actionHelperResolver,
        private ActionConfigurationRepositoryInterface $actionConfigurationRepository,
        private Redirector $redirector,
        private ViewRenderer $viewRenderer,
        private DeleteActionHelperInterface $defaultActionHelper,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param class-string<E> $entityClass
     *
     * @throws CrudEngineEntityManagerNotFoundException
     * @throws CrudEngineInvalidActionHelperException
     * @throws CrudEngineMissingConfigurationException
     * @throws CrudEngineRedirectionParameterRenderingException
     * @throws CrudEngineViewRenderingException
     * @throws Throwable
     */
    public function __invoke(
        Request $request,
        string $entityClass,
        string $id,
    ): Response {
        $actionConfiguration = $this->actionConfigurationRepository->get($entityClass, 'delete');
        $actionHelper        = $this->actionHelperResolver->resolve(
            DeleteActionHelperInterface::class,
            $actionConfiguration,
            $this->defaultActionHelper,
        );

        $entity = $this->getEntity($entityClass, $id);

        if ($request->isMethod('POST')) {
            $entityManager = $this->getEntityManager($entityClass);

            try {
                $actionHelper->hookBeforeRemove($entity);
                $actionHelper->remove($entityManager, $entity);
                $actionHelper->hookAfterRemove($entity);
            } catch (Throwable $e) {
                return $actionHelper->onFailure($entity, $e);
            }

            return $this->redirector->redirect($actionConfiguration, $entity);
        }

        return $this->viewRenderer->render(
            $actionConfiguration,
            array_merge(
                $actionHelper->getViewVariables($request, $entity),
                [
                    'entity' => $entity,
                ],
            ),
        );
    }

    /**
     * @param class-string<E> $entityClass
     *
     * @psalm-return E
     *
     * @throws CrudEngineEntityManagerNotFoundException
     * @throws NotFoundHttpException
     */
    private function getEntity(
        string $entityClass,
        string $id,
    ): object {
        $entity = $this->getEntityManager($entityClass)->find($entityClass, $id);

        return $entity ?? throw new NotFoundHttpException();
    }
}
