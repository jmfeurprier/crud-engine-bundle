<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @template E of object
 */
interface DeleteActionHelperInterface extends ActionHelperInterface
{
    /**
     * @param object<E> $entity
     */
    public function hookBeforeRemove(object $entity): void;

    /**
     * @param object<E> $entity
     */
    public function remove(
        EntityManagerInterface $entityManager,
        object $entity,
    ): void;

    /**
     * @param object<E> $entity
     */
    public function hookAfterRemove(object $entity): void;
}
