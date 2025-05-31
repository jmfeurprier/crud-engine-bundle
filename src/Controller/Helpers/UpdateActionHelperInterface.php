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
     * @param object<E> $entity
     */
    public function hookBeforePersist(
        Request $request,
        object $entity,
    ): void;

    /**
     * @param object<E> $entity
     */
    public function persist(
        Request $request,
        object $entity,
        FormInterface $form,
        ObjectManager $objectManager,
    ): void;

    /**
     * @param object<E> $entity
     */
    public function hookAfterPersist(
        Request $request,
        object $entity,
    ): void;
}
