<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Controller\Traits\WithEntityManagerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeleteAction
{
    use WithEntityManagerTrait;

    public function __construct(
        ManagerRegistry $managerRegistry
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function __invoke(
        string $entityClass,
        string $id
    ): Response {
        $entityManager = $this->getEntityManager($entityClass);
        $entity        = $this->getEntity($entityClass, $id);

        $entityManager->remove($entity);
        $entityManager->flush();

        return new JsonResponse();
    }

    /**
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
