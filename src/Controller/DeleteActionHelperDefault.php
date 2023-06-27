<?php

namespace Jmf\CrudEngine\Controller;

/**
 * @template E of object
 * @implements DeleteActionHelperInterface<E>
 */
class DeleteActionHelperDefault implements DeleteActionHelperInterface
{
    public function hookBeforeRemove(object $entity): void
    {
    }

    public function hookAfterRemove(object $entity): void
    {
    }
}
