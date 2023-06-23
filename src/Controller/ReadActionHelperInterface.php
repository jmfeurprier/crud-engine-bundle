<?php

namespace Jmf\CrudEngine\Controller;

use Symfony\Component\HttpFoundation\Request;

interface ReadActionHelperInterface extends ActionHelperInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getViewVariables(
        Request $request,
        object $entity
    ): array;
}
