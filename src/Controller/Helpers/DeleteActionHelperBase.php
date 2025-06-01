<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Persistence\ObjectManager;
use Override;

/**
 * @template E of object
 * @implements DeleteActionHelperInterface<E>
 */
abstract class DeleteActionHelperBase implements DeleteActionHelperInterface
{
    #[Override]
    public function hookBeforeRemove(object $entity): void
    {
    }

    #[Override]
    public function remove(
        ObjectManager $objectManager,
        object $entity,
    ): void {
        $objectManager->remove($entity);
        $objectManager->flush();
    }

    #[Override]
    public function hookAfterRemove(object $entity): void
    {
    }
}
