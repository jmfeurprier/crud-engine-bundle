<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Persistence\ObjectRepository;
use Override;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 * @implements IndexActionHelperInterface<E>
 */
abstract class IndexActionHelperBase implements IndexActionHelperInterface
{
    #[Override]
    public function hookBeforeRender(Request $request): void
    {
    }

    #[Override]
    public function getEntities(ObjectRepository $entityRepository): iterable
    {
        return $entityRepository->findAll();
    }

    #[Override]
    public function getViewVariables(Request $request): array
    {
        return [];
    }
}
