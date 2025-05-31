<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Persistence\ObjectManager;

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
}
