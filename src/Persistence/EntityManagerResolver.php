<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Persistence;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerTypeMismatchException;

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
     * @throws CrudEngineEntityManagerTypeMismatchException
     */
    public function resolve(string $entityClass): EntityManagerInterface
    {
        $objectManager = $this->managerRegistry->getManagerForClass($entityClass);

        if (!$objectManager instanceof ObjectManager) {
            throw new CrudEngineEntityManagerNotFoundException($entityClass);
        }

        if ($objectManager instanceof EntityManagerInterface) {
            return $objectManager;
        }

        throw new CrudEngineEntityManagerTypeMismatchException($entityClass, $objectManager::class);
    }
}
