<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Persistence\ObjectManager;
use Jmf\CrudEngine\Exception\CrudEngineRuntimeException;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @template E of object
 *
 * @implements DeleteActionHelperInterface<E>
 */
readonly abstract class DeleteActionHelperBase implements DeleteActionHelperInterface
{
    #[Override]
    public function hookBeforeRemove(object $entity): void
    {
    }

    #[Override]
    public function remove(
        ObjectManager $objectManager,
        object $entity,
    ): void {
        $objectManager->remove($entity);
        $objectManager->flush();
    }

    #[Override]
    public function hookAfterRemove(object $entity): void
    {
    }

    #[Override]
    public function getViewVariables(
        Request $request,
        object $entity,
    ): array {
        return [];
    }

    #[Override]
    public function onFailure(
        object $entity,
        Throwable $e,
    ): Response {
        // @todo Create specialized exception?
        throw new CrudEngineRuntimeException(
            message:  'Failed deleting entity.',
            previous: $e,
        );
    }
}
