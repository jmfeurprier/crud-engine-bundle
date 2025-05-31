<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Persistence\ObjectManager;
use Override;
use Symfony\Component\Form\FormInterface;
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
    public function hookBeforePersist(
        Request $request,
        object $entity,
        FormInterface $form,
    ): void {
    }

    #[Override]
    public function persist(
        Request $request,
        object $entity,
        FormInterface $form,
        ObjectManager $entityManager,
    ): void {
        $entityManager->persist($entity);
        $entityManager->flush();
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
