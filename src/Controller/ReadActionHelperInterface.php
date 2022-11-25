<?php

namespace Jmf\CrudEngine\Controller;

use Symfony\Component\HttpFoundation\Request;

interface ReadActionHelperInterface
{
    public function getViewVariables(
        Request $request,
        object $entity
    ): array;
}
