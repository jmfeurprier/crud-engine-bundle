<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Persistence;

use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerTypeMismatchException;
use Jmf\CrudEngine\Exception\CrudEngineEntityNotFoundException;
use Jmf\CrudEngine\Exception\CrudEnginePersistenceException;
use Throwable;

readonly class EntityFinder
{
    public function __construct(
        private EntityManagerResolver $entityManagerResolver,
    ) {
    }

    /**
     * @template E of object
     *
     * @param class-string<E> $entityClass
     *
     * @return E
     *
     * @throws CrudEngineEntityManagerNotFoundException
     * @throws CrudEngineEntityManagerTypeMismatchException
     * @throws CrudEnginePersistenceException
     * @throws CrudEngineEntityNotFoundException
     */
    public function find(
        string $entityClass,
        string $id,
    ): object {
        $objectManager = $this->entityManagerResolver->resolve($entityClass);

        try {
            $entity = $objectManager->find($entityClass, $id);
        } catch (Throwable $e) {
            throw new CrudEnginePersistenceException($entityClass, $e);
        }

        return $entity ?? throw new CrudEngineEntityNotFoundException($entityClass, $id);
    }
}
