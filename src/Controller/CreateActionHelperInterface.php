<?php

namespace Jmf\CrudEngine\Controller;

use Jmf\CrudEngine\Exception\CrudEngineInstantiationFailureException;
use Symfony\Component\HttpFoundation\Request;

interface CreateActionHelperInterface extends ActionHelperInterface
{
    /**
     * @template T
     *
     * @param class-string<T> $entityClass
     *
     * @return T
     *
     * @throws CrudEngineInstantiationFailureException
     */
    public function createEntity(
        Request $request,
        string $entityClass
    ): object;

    public function hookAfterPersist(
        Request $request,
        object $entity
    ): void;
}
