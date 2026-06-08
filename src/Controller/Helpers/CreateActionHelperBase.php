<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Persistence\ObjectManager;
use Jmf\CrudEngine\Exception\CrudEngineInstantiationFailureException;
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
    /**
     * @throws CrudEngineInstantiationFailureException
     */
    #[Override]
    public function createEntity(
        Request $request,
        string $entityClass,
    ): object {
        // Deliberately naive: keeping this concrete (and the base constructor-free) means extending
        // CreateActionHelperBase never forces a createEntity() implementation for the common case of
        // a simple, no-arg-constructor entity. The zero-config path (no custom helper) goes through
        // CreateActionHelperDefault, which instantiates via the Doctrine Instantiator instead.
        try {
            return new $entityClass();
        } catch (Throwable $e) {
            throw new CrudEngineInstantiationFailureException($entityClass, $e);
        }
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
