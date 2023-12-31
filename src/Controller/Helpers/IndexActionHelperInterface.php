<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 */
interface IndexActionHelperInterface extends ActionHelperInterface
{
    public function hookBeforeRender(Request $request): void;

    /**
     * @param ObjectRepository<E> $entityRepository
     *
     * @return E[]
     */
    public function getEntities(ObjectRepository $entityRepository): iterable;

    /**
     * @return array<string, mixed>
     */
    public function getViewVariables(Request $request): array;
}
