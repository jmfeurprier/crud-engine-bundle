<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Model;

readonly class EntityAction
{
    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     */
    public function __construct(
        private string $entityClass,
        private string $action,
    ) {
    }

    /**
     * @return class-string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @return non-empty-string
     */
    public function getAction(): string
    {
        return $this->action;
    }
}
