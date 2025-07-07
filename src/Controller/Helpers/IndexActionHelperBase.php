<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Persistence\ObjectManager;
use Override;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 * @implements IndexActionHelperInterface<E>
 */
abstract class IndexActionHelperBase implements IndexActionHelperInterface
{
    #[Override]
    public function hookBeforeRender(
        Request $request,
    ): void {
    }

    #[Override]
    public function getEntities(
        Request $request,
        string $entityClass,
        ObjectManager $entityManager,
    ): iterable {
        return $entityManager->getRepository($entityClass)->findAll();
    }

    #[Override]
    public function getViewVariables(Request $request): array
    {
        return [];
    }
}
