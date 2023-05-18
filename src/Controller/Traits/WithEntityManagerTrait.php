<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

trait WithEntityManagerTrait
{
    private ManagerRegistry $managerRegistry;

    private function getEntityManager(string $entityClass): ObjectManager
    {
        return $this->managerRegistry->getManagerForClass($entityClass);
    }

    private function getRepository(string $entityClass): ObjectRepository
    {
        return $this->managerRegistry->getRepository($entityClass);
    }
}
