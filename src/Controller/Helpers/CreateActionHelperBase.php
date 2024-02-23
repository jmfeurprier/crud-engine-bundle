<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Override;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 * @implements CreateActionHelperInterface<E>
 */
abstract class CreateActionHelperBase implements CreateActionHelperInterface
{
    #[Override]
    public function createEntity(
        Request $request,
        string $entityClass,
    ): object {
        return new $entityClass();
    }

    #[Override]
    public function hookAfterPersist(
        Request $request,
        object $entity,
    ): void {
    }

    #[Override]
    public function getViewVariables(
        Request $request,
        object $entity,
    ): array {
        return [];
    }
}
