<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Override;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 * @implements ReadActionHelperInterface<E>
 */
abstract class ReadActionHelperBase implements ReadActionHelperInterface
{
    #[Override]
    public function getViewVariables(
        Request $request,
        object $entity,
    ): array {
        return [];
    }
}
