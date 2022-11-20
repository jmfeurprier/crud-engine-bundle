<?php

namespace Jmf\CrudEngine\Action;

use Symfony\Component\HttpFoundation\Request;

interface CreateActionHelperInterface
{
    public function createEntity(
        Request $request,
        string $entityClass
    ): object;
}
