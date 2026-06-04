<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Persistence\ObjectManager;
use Jmf\CrudEngine\Exception\CrudEnginePersistenceException;
use Override;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * @template E of object
 *
 * @implements CreateActionHelperInterface<E>
 */
readonly abstract class CreateActionHelperBase implements CreateActionHelperInterface
{
    #[Override]
    public function createEntity(
        Request $request,
        string $entityClass,
    ): object {
        // @xxx
        return new $entityClass();
    }

    #[Override]
    public function hookBeforePersist(
        Request $request,
        object $entity,
        FormInterface $form,
    ): void {
    }

    /**
     * @throws CrudEnginePersistenceException
     */
    #[Override]
    public function persist(
        Request $request,
        object $entity,
        FormInterface $form,
        ObjectManager $objectManager,
    ): void {
        try {
            $objectManager->persist($entity);
            $objectManager->flush();
        } catch (Throwable $e) {
            throw new CrudEnginePersistenceException($entity::class, $e);
        }
    }

    #[Override]
    public function hookAfterPersist(
        Request $request,
        object $entity,
        FormInterface $form,
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
