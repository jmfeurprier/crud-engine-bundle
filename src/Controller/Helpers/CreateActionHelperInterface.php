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
     * @return E
     *
     * @throws CrudEngineInstantiationFailureException
     */
    public function createEntity(
        Request $request,
        string $entityClass,
    ): object;

    /**
     * @param E $entity
     */
    public function hookAfterPersist(
        Request $request,
        object $entity,
    ): void;

    /**
     * @param E $entity
     *
     * @return array<string, mixed>
     */
    public function getViewVariables(
        Request $request,
        object $entity
    ): array;
}
