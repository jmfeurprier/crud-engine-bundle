<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Instantiator\Exception\ExceptionInterface;
use Doctrine\Instantiator\InstantiatorInterface;
use Jmf\CrudEngine\Exception\CrudEngineInstantiationFailureException;
use Override;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 * @extends  CreateActionHelperBase<E>
 */
final class CreateActionHelperDefault extends CreateActionHelperBase
{
    public function __construct(
        private readonly InstantiatorInterface $instantiator,
    ) {
    }

    #[Override]
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
}
