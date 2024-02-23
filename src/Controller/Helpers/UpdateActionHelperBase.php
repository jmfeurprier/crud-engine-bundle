<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Override;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 * @implements UpdateActionHelperInterface<E>
 */
abstract class UpdateActionHelperBase implements UpdateActionHelperInterface
{
    #[Override]
    public function hookAfterPersist(
        Request $request,
        object $entity,
    ): void {
    }
}
