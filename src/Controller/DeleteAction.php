<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\DeleteActionHelperInterface;
use Jmf\CrudEngine\Controller\Traits\WithActionHelperTrait;
use Jmf\CrudEngine\Controller\Traits\WithEntityManagerTrait;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @template E of object
 */
#[AsController]
class DeleteAction
{
    /**
     * @use WithActionHelperTrait<DeleteActionHelperInterface<E>>
     */
    use WithActionHelperTrait;
    use WithEntityManagerTrait;

    /**
     * @param DeleteActionHelperInterface<E> $defaultActionHelper
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        DeleteActionHelperInterface $defaultActionHelper,
        ActionHelperResolver $actionHelperResolver,
        private readonly ActionConfigurationRepositoryInterface $actionConfigurationRepository,
    ) {
        $this->managerRegistry      = $managerRegistry;
        $this->defaultActionHelper  = $defaultActionHelper;
        $this->actionHelperResolver = $actionHelperResolver;
    }

    /**
     * @param class-string<E> $entityClass
     *
     * @throws CrudEngineEntityManagerNotFoundException
     * @throws CrudEngineInvalidActionHelperException
     */
    public function __invoke(
        string $entityClass,
        string $id,
    ): Response {
        $actionConfiguration = $this->actionConfigurationRepository->get($entityClass, 'delete');
        $actionHelper        = $this->getActionHelper(
            DeleteActionHelperInterface::class,
            $actionConfiguration,
        );

        $entity = $this->getEntity($entityClass, $id);

        $actionHelper->hookBeforeRemove($entity);

        $entityManager = $this->getEntityManager($entityClass);
        $entityManager->remove($entity);
        $entityManager->flush();

        $actionHelper->hookAfterRemove($entity);

        return new JsonResponse();
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

        if ($entity) {
            return $entity;
        }

        throw new NotFoundHttpException();
    }
}
