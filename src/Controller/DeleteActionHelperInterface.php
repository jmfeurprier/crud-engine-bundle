<?php

namespace Jmf\CrudEngine\Controller;

/**
 * @template E of object
 */
interface DeleteActionHelperInterface extends ActionHelperInterface
{
    /**
     * @param E $entity
     */
    public function hookBeforeRemove(object $entity): void;

    /**
     * @param E $entity
     */
    public function hookAfterRemove(object $entity): void;
}
