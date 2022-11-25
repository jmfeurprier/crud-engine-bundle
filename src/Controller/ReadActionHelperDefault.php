<?php

namespace Jmf\CrudEngine\Controller;

use Symfony\Component\HttpFoundation\Request;

class ReadActionHelperDefault implements ReadActionHelperInterface
{
    public function getViewVariables(
        Request $request,
        object $entity
    ): array {
        return [];
    }
}
