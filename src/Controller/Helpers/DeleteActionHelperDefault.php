<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Override;

/**
 * @template E of object
 * @implements DeleteActionHelperInterface<E>
 */
class DeleteActionHelperDefault implements DeleteActionHelperInterface
{
    #[Override]
    public function hookBeforeRemove(object $entity): void
    {
    }

    #[Override]
    public function hookAfterRemove(object $entity): void
    {
    }
}
