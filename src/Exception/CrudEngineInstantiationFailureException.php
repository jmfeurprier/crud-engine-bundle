<?php

namespace Jmf\CrudEngine\Exception;

use Throwable;

class CrudEngineInstantiationFailureException extends CrudEngineException
{
    /**
     * @param class-string $entityClass
     */
    public function __construct(
        private readonly string $entityClass,
        ?Throwable $previousException = null
    ) {
        parent::__construct("Failed instantiating entity of class {$entityClass}", 0, $previousException);
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}
