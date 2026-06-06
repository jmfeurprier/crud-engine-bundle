<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Throwable;

final class CrudEngineFormRequestHandlingException extends CrudEngineFormException
{
    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     */
    public function __construct(
        private readonly string $entityClass,
        private readonly string $action,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message:  sprintf(
                          'Failed handling the form request for entity "%s" for action "%s".',
                          $entityClass,
                          $action,
                      ),
            previous: $previous,
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
     * @return non-empty-string
     */
    public function getAction(): string
    {
        return $this->action;
    }
}
