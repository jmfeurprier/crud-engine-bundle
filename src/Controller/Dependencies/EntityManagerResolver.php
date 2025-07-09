<?php

namespace Jmf\CrudEngine\Controller\Dependencies;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;

readonly class EntityManagerResolver
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    /**
     * @param class-string $entityClass
     *
     * @throws CrudEngineEntityManagerNotFoundException
     */
    public function resolve(string $entityClass): ObjectManager
    {
        $entityManager = $this->managerRegistry->getManagerForClass($entityClass);

        if (null === $entityManager) {
            throw new CrudEngineEntityManagerNotFoundException($entityClass);
        }

        return $entityManager;
    }
}
