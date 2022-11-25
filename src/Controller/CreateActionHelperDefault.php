<?php

namespace Jmf\CrudEngine\Controller;

use Symfony\Component\HttpFoundation\Request;

class CreateActionHelperDefault implements CreateActionHelperInterface
{
    public function createEntity(Request $request, string $entityClass): object
    {
        return new $entityClass();
    }
}
