<?php

namespace Jmf\CrudEngine\Controller\Helpers;

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
     * @return object<E>
     *
     * @throws CrudEngineInstantiationFailureException
     */
    public function createEntity(
        Request $request,
        string $entityClass,
    ): object;

    /**
     * @param object<E> $entity
     */
    public function hookAfterPersist(
        Request $request,
        object $entity,
    ): void;

    /**
     * @param object<E> $entity
     *
     * @return array<string, mixed>
     */
    public function getViewVariables(
        Request $request,
        object $entity
    ): array;
}
