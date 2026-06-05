<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Persistence;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
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
    public function resolve(string $entityClass): EntityManagerInterface
    {
        $objectManager = $this->managerRegistry->getManagerForClass($entityClass);

        if (null === $objectManager) {
            throw new CrudEngineEntityManagerNotFoundException($entityClass);
        }

        if ($objectManager instanceof EntityManagerInterface) {
            return $objectManager;
        }

        // @xxx
        throw new CrudEngineEntityManagerNotFoundException($entityClass);
    }
}
