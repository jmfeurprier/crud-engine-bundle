<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Persistence\ObjectManager;
use Jmf\CrudEngine\Exception\CrudEngineException;
use Override;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @template E of object
 * @implements DeleteActionHelperInterface<E>
 */
abstract class DeleteActionHelperBase implements DeleteActionHelperInterface
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
    public function onSuccess(object $entity): JsonResponse
    {
        return new JsonResponse();
    }

    /**
     * @throws CrudEngineException
     */
    #[Override]
    public function onFailure(
        object $entity,
        Throwable $e,
    ): Response {
        // @todo Create specialized exception.
        throw new CrudEngineException(
            message:  'Failed deleting entity.',
            previous: $e,
        );
    }
}
