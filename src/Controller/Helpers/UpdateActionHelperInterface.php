<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Jmf\CrudEngine\Exception\CrudEngineInstantiationFailureException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 */
interface UpdateActionHelperInterface extends ActionHelperInterface
{
    /**
     * @param object<E> $entity
     */
    public function hookBeforePersist(
        Request $request,
        object $entity,
    ): void;

    /**
     * @param object<E> $entity
     */
    public function hookAfterPersist(
        Request $request,
        object $entity,
    ): void;
}
