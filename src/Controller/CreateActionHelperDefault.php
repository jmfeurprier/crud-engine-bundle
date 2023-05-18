<?php

namespace Jmf\CrudEngine\Controller;

use Symfony\Component\HttpFoundation\Request;

class CreateActionHelperDefault implements CreateActionHelperInterface
{
    public function createEntity(
        Request $request,
        string $entityClass
    ): object {
        // @todo Use doctrine/instantiator here.
        return new $entityClass();
    }

    public function hookAfterPersist(
        Request $request,
        object $entity
    ): void {
    }
}
