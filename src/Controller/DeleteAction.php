<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Controller\Traits\WithActionHelperTrait;
use Jmf\CrudEngine\Controller\Traits\WithEntityManagerTrait;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @template E of object
 */
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
        ContainerInterface $container
    ) {
        $this->managerRegistry     = $managerRegistry;
        $this->defaultActionHelper = $defaultActionHelper;
        $this->container           = $container;
    }

    /**
     * @param array<string,mixed> $actionProperties
     * @param class-string<E>     $entityClass
     *
     * @throws CrudEngineEntityManagerNotFoundException
     * @throws CrudEngineInvalidActionHelperException
     * @throws CrudEngineInvalidConfigurationException
     */
    public function __invoke(
        array $actionProperties,
        string $entityClass,
        string $id
    ): Response {
        $actionHelper = $this->getActionHelper(DeleteActionHelperInterface::class, $actionProperties);

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
     * @return E
     *
     * @throws NotFoundHttpException
     */
    private function getEntity(
        string $entityClass,
        string $id
    ): object {
        $entity = $this->getRepository($entityClass)->find($id);

        if ($entity) {
            return $entity;
        }

        throw new NotFoundHttpException();
    }
}
