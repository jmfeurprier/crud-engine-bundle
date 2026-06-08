<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Persistence\ObjectManager;
use Jmf\CrudEngine\Exception\CrudEngineInstantiationFailureException;
use Jmf\CrudEngine\Exception\CrudEnginePersistenceException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 */
interface CreateActionHelperInterface extends ActionHelperInterface
{
    /**
     * @param class-string<E> $entityClass
     *
     * @return E
     *
     * @throws CrudEngineInstantiationFailureException
     */
    public function createEntity(
        Request $request,
        string $entityClass,
    ): object;

    /**
     * @param E                $entity
     * @param FormInterface<E> $form
     */
    public function hookBeforePersist(
        Request $request,
        object $entity,
        FormInterface $form,
    ): void;

    /**
     * @param E                $entity
     * @param FormInterface<E> $form
     *
     * @throws CrudEnginePersistenceException
     */
    public function persist(
        Request $request,
        object $entity,
        FormInterface $form,
        ObjectManager $objectManager,
    ): void;

    /**
     * @param E                $entity
     * @param FormInterface<E> $form
     */
    public function hookAfterPersist(
        Request $request,
        object $entity,
        FormInterface $form,
    ): void;

    /**
     * @param E $entity
     *
     * @return array<string, mixed>
     */
    public function getViewVariables(
        Request $request,
        object $entity,
    ): array;
}
