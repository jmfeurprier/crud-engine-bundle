<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;

trait WithEntityManagerTrait
{
    private readonly ManagerRegistry $managerRegistry;

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
}
