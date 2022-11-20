<?php

namespace Jmf\CrudEngine\Action;

use Symfony\Component\HttpFoundation\Request;

class CreateActionHelperDefault implements CreateActionHelperInterface
{
    public function createEntity(Request $request, string $entityClass): object
    {
        return new $entityClass();
    }
}
