<?php

namespace Jmf\CrudEngine\Action;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeleteAction extends AbstractController
{
    private ManagerRegistry $managerRegistry;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ManagerRegistry $managerRegistry
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function __invoke(
        string $entityClass,
        string $id
    ): Response {
        $this->entityManager = $this->managerRegistry->getManagerForClass($entityClass);
        $entity              = $this->getEntity($entityClass, $id);

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new JsonResponse();
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getEntity(
        string $entityClass,
        string $id
    ): object {
        $entity = $this->entityManager->find($entityClass, $id);

        if ($entity) {
            return $entity;
        }

        throw new NotFoundHttpException();
    }
}
