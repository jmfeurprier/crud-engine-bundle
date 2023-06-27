<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 * @implements IndexActionHelperInterface<E>
 */
class IndexActionHelperDefault implements IndexActionHelperInterface
{
    public function hookBeforeRender(Request $request): void
    {
    }

    public function getEntities(ObjectRepository $entityRepository): array
    {
        return $entityRepository->findAll();
    }

    public function getViewVariables(Request $request): array
    {
        return [];
    }
}
