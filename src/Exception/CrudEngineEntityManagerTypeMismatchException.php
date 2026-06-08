<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

class CrudEngineEntityManagerTypeMismatchException extends CrudEngineRuntimeException
{
    /**
     * @param class-string $entityClass
     * @param class-string $actualManagerClass
     */
    public function __construct(
        private readonly string $entityClass,
        private readonly string $actualManagerClass,
    ) {
        parent::__construct(
            message: sprintf(
                         'Manager for class %s is not a Doctrine ORM EntityManager (got %s).',
                         $entityClass,
                         $actualManagerClass,
                     ),
        );
    }

    /**
     * @return class-string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @return class-string
     */
    public function getActualManagerClass(): string
    {
        return $this->actualManagerClass;
    }
}
