<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

class CrudEngineEntityNotFoundException extends CrudEngineRuntimeException
{
    /**
     * @param class-string $entityClass
     */
    public function __construct(
        private readonly string $entityClass,
        private readonly string $id,
    ) {
        parent::__construct(
            message: sprintf(
                         'Entity of class %s with id "%s" not found.',
                         $entityClass,
                         $id,
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

    public function getId(): string
    {
        return $this->id;
    }
}
