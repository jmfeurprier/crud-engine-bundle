<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Persistence;

use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEnginePersistenceException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

readonly class EntityFinder
{
    public function __construct(
        private EntityManagerResolver $objectManagerResolver,
    ) {
    }

    /**
     * @template E of object
     *
     * @param class-string<E> $entityClass
     *
     * @psalm-return E
     *
     * @throws CrudEngineEntityManagerNotFoundException
     * @throws CrudEnginePersistenceException
     * @throws NotFoundHttpException
     */
    public function find(
        string $entityClass,
        string $id,
    ): object {
        $objectManager = $this->objectManagerResolver->resolve($entityClass);

        try {
            $entity = $objectManager->find($entityClass, $id);
        } catch (Throwable $e) {
            throw new CrudEnginePersistenceException($entityClass, $e);
        }

        return $entity ?? throw new NotFoundHttpException();
    }
}
