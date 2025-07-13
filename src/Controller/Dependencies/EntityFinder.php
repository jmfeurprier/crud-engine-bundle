<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller\Dependencies;

use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @psalm-return E
     *
     * @throws CrudEngineEntityManagerNotFoundException
     * @throws NotFoundHttpException
     */
    public function find(
        string $entityClass,
        string $id,
    ): object {
        $entity = $this->entityManagerResolver->resolve($entityClass)->find($entityClass, $id);

        return $entity ?? throw new NotFoundHttpException();
    }
}
