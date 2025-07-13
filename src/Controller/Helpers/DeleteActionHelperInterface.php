<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @template E of object
 */
interface DeleteActionHelperInterface extends ActionHelperInterface
{
    /**
     * @psalm-param E $entity
     */
    public function hookBeforeRemove(object $entity): void;

    /**
     * @psalm-param E $entity
     */
    public function remove(
        ObjectManager $objectManager,
        object $entity,
    ): void;

    /**
     * @psalm-param E $entity
     */
    public function hookAfterRemove(object $entity): void;

    /**
     * @psalm-param E $entity
     *
     * @return array<string, mixed>
     */
    public function getViewVariables(
        Request $request,
        object $entity,
    ): array;

    /**
     * @psalm-param E $entity
     *
     * @throws Throwable
     */
    public function onFailure(
        object $entity,
        Throwable $e,
    ): Response;
}
