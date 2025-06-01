<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Persistence\ObjectManager;
use Jmf\CrudEngine\Exception\CrudEngineException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @template E of object
 */
interface DeleteActionHelperInterface extends ActionHelperInterface
{
    /**
     * @psalm-param E $entity
     */
    public function hookBeforeRemove(object $entity): void;

    /**
     * @psalm-param E $entity
     */
    public function remove(
        ObjectManager $objectManager,
        object $entity,
    ): void;

    /**
     * @psalm-param E $entity
     */
    public function hookAfterRemove(object $entity): void;

    /**
     * @psalm-param E $entity
     */
    public function onSuccess(object $entity): Response;

    /**
     * @psalm-param E $entity
     */
    public function onFailure(
        object $entity,
        Throwable $e,
    ): Response;
}
