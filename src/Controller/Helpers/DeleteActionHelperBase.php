<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\ORM\EntityManagerInterface;
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
        EntityManagerInterface $entityManager,
        object $entity,
    ): void {
        $entityManager->remove($entity);
        $entityManager->flush();
    }

    #[Override]
    public function hookAfterRemove(object $entity): void
    {
    }
}
