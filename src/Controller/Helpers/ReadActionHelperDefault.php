<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Override;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 * @implements ReadActionHelperInterface<E>
 */
class ReadActionHelperDefault implements ReadActionHelperInterface
{
    #[Override]
    public function getViewVariables(
        Request $request,
        object $entity
    ): array {
        return [];
    }
}
