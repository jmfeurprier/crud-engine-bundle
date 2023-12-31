<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 * @implements ReadActionHelperInterface<E>
 */
class ReadActionHelperDefault implements ReadActionHelperInterface
{
    public function getViewVariables(
        Request $request,
        object $entity
    ): array {
        return [];
    }
}
