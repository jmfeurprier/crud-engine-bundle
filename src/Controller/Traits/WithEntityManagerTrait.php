<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;

trait WithEntityManagerTrait
{
    private ManagerRegistry $managerRegistry;

    /**
     * @param class-string $entityClass
     *
     * @throws CrudEngineEntityManagerNotFoundException
     */
    private function getEntityManager(string $entityClass): ObjectManager
    {
        $entityManager = $this->managerRegistry->getManagerForClass($entityClass);

        if (null === $entityManager) {
            throw new CrudEngineEntityManagerNotFoundException($entityClass);
        }

        return $entityManager;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $entityClass
     *
     * @return ObjectRepository<T>
     */
    private function getRepository(string $entityClass): ObjectRepository
    {
        return $this->managerRegistry->getRepository($entityClass);
    }
}
