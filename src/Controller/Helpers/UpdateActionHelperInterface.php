<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 */
interface UpdateActionHelperInterface extends ActionHelperInterface
{
    /**
     * @psalm-param E                $entity
     * @psalm-param FormInterface<E> $form
     */
    public function hookBeforePersist(
        Request $request,
        object $entity,
        FormInterface $form,
    ): void;

    /**
     * @psalm-param E                $entity
     * @psalm-param FormInterface<E> $form
     */
    public function persist(
        Request $request,
        object $entity,
        FormInterface $form,
        ObjectManager $objectManager,
    ): void;

    /**
     * @psalm-param E                $entity
     * @psalm-param FormInterface<E> $form
     */
    public function hookAfterPersist(
        Request $request,
        object $entity,
        FormInterface $form,
    ): void;
}
