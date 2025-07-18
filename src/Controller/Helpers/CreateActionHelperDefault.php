<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller\Helpers;

use Doctrine\Instantiator\Exception\ExceptionInterface;
use Doctrine\Instantiator\InstantiatorInterface;
use Jmf\CrudEngine\Exception\CrudEngineInstantiationFailureException;
use Override;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template E of object
 *
 * @extends  CreateActionHelperBase<E>
 */
final readonly class CreateActionHelperDefault extends CreateActionHelperBase
{
    public function __construct(
        private InstantiatorInterface $instantiator,
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
