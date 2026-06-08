<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Definition\ActionDefinition;

class CrudEngineActionHelperNotAnObjectException extends CrudEngineRuntimeException
{
    public function __construct(
        private readonly ActionDefinition $actionDefinition,
    ) {
        parent::__construct(
            message: sprintf(
                         'Retrieved Action Helper for Entity %s and Action %s is not an object.',
                         $this->actionDefinition->getEntityAction()->getEntityClass(),
                         $this->actionDefinition->getEntityAction()->getAction()->value,
                     ),
        );
    }

    public function getActionDefinition(): ActionDefinition
    {
        return $this->actionDefinition;
    }
}
