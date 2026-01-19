<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Throwable;

class CrudEngineEntityManagerNotFoundException extends CrudEngineRuntimeException
{
    /**
     * @param class-string $entityClass
     */
    public function __construct(
        private readonly string $entityClass,
        ?Throwable $previousException = null,
    ) {
        parent::__construct(
            message:  "Entity manager not found for class {$entityClass}",
            previous: $previousException,
        );
    }

    /**
     * @return class-string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}
