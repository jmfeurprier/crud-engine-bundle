<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;

class IndexActionHelperDefault implements IndexActionHelperInterface
{
    public function hookPre(Request $request): void
    {
    }

    public function getEntities(ObjectRepository $entityRepository): array
    {
        return $entityRepository->findAll();
    }

    public function getViewParameters(Request $request): array
    {
        return [];
    }
}
