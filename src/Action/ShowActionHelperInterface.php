<?php

namespace Jmf\CrudEngine\Action;

use Symfony\Component\HttpFoundation\Request;

interface ShowActionHelperInterface
{
    public function getViewParameters(
        Request $request,
        object $entity
    ): array;
}
