<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;

interface IndexActionHelperInterface
{
    public function hookBeforeRender(Request $request): void;

    public function getEntities(ObjectRepository $entityRepository): array;

    public function getViewVariables(Request $request): array;
}
