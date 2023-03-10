<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

interface CreateActionHelperInterface
{
    public function createEntity(
        Request $request,
        string $entityClass
    ): object;

    public function hookAfterPersist(
        Request $request,
        object $entity
    ): void;
}
