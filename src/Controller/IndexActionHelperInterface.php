<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;

interface IndexActionHelperInterface extends ActionHelperInterface
{
    public function hookBeforeRender(Request $request): void;

    /**
     * @template T of object
     *
     * @param ObjectRepository<T> $entityRepository
     *
     * @return T[]
     */
    public function getEntities(ObjectRepository $entityRepository): array;

    /**
     * @return array<string, mixed>
     */
    public function getViewVariables(Request $request): array;
}
