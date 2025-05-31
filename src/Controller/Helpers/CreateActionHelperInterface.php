<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Persistence\ObjectManager;
use Jmf\CrudEngine\Exception\CrudEngineInstantiationFailureException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 */
interface CreateActionHelperInterface extends ActionHelperInterface
{
    /**
     * @param class-string<E> $entityClass
     *
     * @psalm-return E
     *
     * @throws CrudEngineInstantiationFailureException
     */
    public function createEntity(
        Request $request,
        string $entityClass,
    ): object;

    /**
     * @psalm-param E $entity
     */
    public function hookBeforePersist(
        Request $request,
        object $entity,
    ): void;

    /**
     * @psalm-param E $entity
     */
    public function persist(
        Request $request,
        object $entity,
        ObjectManager $entityManager,
    ): void;

    /**
     * @psalm-param E $entity
     */
    public function hookAfterPersist(
        Request $request,
        object $entity,
    ): void;

    /**
     * @psalm-param E $entity
     *
     * @return array<string, mixed>
     */
    public function getViewVariables(
        Request $request,
        object $entity,
    ): array;
}
