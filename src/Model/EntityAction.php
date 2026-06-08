<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Model;

readonly class EntityAction
{
    /**
     * @param class-string $entityClass
     */
    public function __construct(
        private string $entityClass,
        private CrudAction $action,
    ) {
    }

    /**
     * @return class-string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getAction(): CrudAction
    {
        return $this->action;
    }
}
