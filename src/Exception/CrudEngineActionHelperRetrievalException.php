<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Throwable;

class CrudEngineActionHelperRetrievalException extends CrudEngineRuntimeException
{
    /**
     * @param class-string $helperClass
     */
    public function __construct(
        private readonly ActionDefinition $actionDefinition,
        private readonly string $helperClass,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message:  sprintf(
                          'Failed retrieving Action Helper %s for Entity %s and Action %s from container.',
                          $this->helperClass,
                          $this->actionDefinition->getEntityAction()->getEntityClass(),
                          $this->actionDefinition->getEntityAction()->getAction(),
                      ),
            previous: $previous,
        );
    }

    public function getActionDefinition(): ActionDefinition
    {
        return $this->actionDefinition;
    }

    public function getHelperClass(): string
    {
        return $this->helperClass;
    }
}
