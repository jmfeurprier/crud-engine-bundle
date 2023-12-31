<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 */
interface ReadActionHelperInterface extends ActionHelperInterface
{
    /**
     * @param E $entity
     *
     * @return array<string, mixed>
     */
    public function getViewVariables(
        Request $request,
        object $entity
    ): array;
}
