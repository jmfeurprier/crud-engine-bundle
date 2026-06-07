<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Model\ActionDefinition;

class CrudEngineActionHelperNotFoundException extends CrudEngineRuntimeException
{
    /**
     * @param class-string $helperClass
     */
    public function __construct(
        private readonly ActionDefinition $actionDefinition,
        private readonly string $helperClass,
    ) {
        parent::__construct(
            message: sprintf(
                         'Action Helper %s for Entity %s and Action %s not found.',
                         $this->helperClass,
                         $this->actionDefinition->getEntityAction()->getEntityClass(),
                         $this->actionDefinition->getEntityAction()->getAction(),
                     ),
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
