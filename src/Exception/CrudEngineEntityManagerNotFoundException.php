<?php

namespace Jmf\CrudEngine\Exception;

use Throwable;

class CrudEngineEntityManagerNotFoundException extends CrudEngineException
{
    /**
     * @var class-string
     */
    private string $entityClass;

    /**
     * @param class-string $entityClass
     */
    public function __construct(
        string $entityClass,
        ?Throwable $previousException = null
    ) {
        parent::__construct("Entity manager not found for class {$entityClass}", 0, $previousException);

        $this->entityClass = $entityClass;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}
