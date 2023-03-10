<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class CreateActionHelperDefault implements CreateActionHelperInterface
{
    public function createEntity(Request $request, string $entityClass): object
    {
        return new $entityClass();
    }

    public function hookAfterPersist(
        Request $request,
        object $entity
    ): void {
    }
}
