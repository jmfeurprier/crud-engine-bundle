<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Persistence\ObjectManager;
use Jmf\CrudEngine\Exception\CrudEngineExceptionInterface;
use Jmf\CrudEngine\Exception\CrudEnginePersistenceException;
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

    /**
     * @throws CrudEnginePersistenceException
     */
    #[Override]
    public function remove(
        ObjectManager $objectManager,
        object $entity,
    ): void {
        try {
            $objectManager->remove($entity);
            $objectManager->flush();
        } catch (Throwable $e) {
            throw new CrudEnginePersistenceException($entity::class, $e);
        }
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

    /**
     * @throws CrudEngineExceptionInterface
     */
    #[Override]
    public function onFailure(
        object $entity,
        Throwable $e,
    ): Response {
        if ($e instanceof CrudEngineExceptionInterface) {
            throw $e;
        }

        throw new CrudEnginePersistenceException($entity::class, $e);
    }
}
