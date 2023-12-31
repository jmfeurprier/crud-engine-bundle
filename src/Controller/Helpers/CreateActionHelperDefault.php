<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Instantiator\Exception\ExceptionInterface;
use Doctrine\Instantiator\InstantiatorInterface;
use Jmf\CrudEngine\Exception\CrudEngineInstantiationFailureException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 * @implements CreateActionHelperInterface<E>
 */
readonly class CreateActionHelperDefault implements CreateActionHelperInterface
{
    public function __construct(
        private InstantiatorInterface $instantiator,
    ) {
    }

    public function createEntity(
        Request $request,
        string $entityClass,
    ): object {
        try {
            return $this->instantiator->instantiate($entityClass);
        } catch (ExceptionInterface $e) {
            throw new CrudEngineInstantiationFailureException($entityClass, $e);
        }
    }

    public function hookAfterPersist(
        Request $request,
        object $entity,
    ): void {
    }
}
