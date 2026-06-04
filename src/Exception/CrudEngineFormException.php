<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Throwable;

class CrudEngineFormException extends CrudEngineRuntimeException
{
    /**
     * @param class-string $entityClass
     */
    public function __construct(
        private readonly string $entityClass,
        ?Throwable $previousException = null,
    ) {
        parent::__construct(
            message:  sprintf(
                          'Failed building or handling the form for entity "%s".',
                          $this->entityClass,
                      ),
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
