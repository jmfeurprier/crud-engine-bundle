<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 */
interface IndexActionHelperInterface extends ActionHelperInterface
{
    public function hookBeforeRender(Request $request): void;

    /**
     * @param class-string<E> $entityClass
     *
     * @return E[]
     */
    public function getEntities(
        Request $request,
        string $entityClass,
        ObjectManager $objectManager,
    ): iterable;

    /**
     * @return array<string, mixed>
     */
    public function getViewVariables(Request $request): array;
}
