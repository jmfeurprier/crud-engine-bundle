<?php

namespace Jmf\CrudEngine\Action;

use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;

interface IndexActionHelperInterface
{
    public function hookPre(Request $request): void;

    public function getEntities(ObjectRepository $entityRepository): array;

    public function getViewParameters(Request $request): array;
}
