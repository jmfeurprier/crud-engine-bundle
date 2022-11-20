<?php

namespace Jmf\CrudEngine\Action;

use Symfony\Component\HttpFoundation\Request;

class ShowActionHelperDefault implements ShowActionHelperInterface
{
    public function getViewParameters(
        Request $request,
        object $entity
    ): array {
        return [];
    }
}
