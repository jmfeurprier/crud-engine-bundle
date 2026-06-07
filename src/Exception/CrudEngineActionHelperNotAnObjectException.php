<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Model\ActionDefinition;

class CrudEngineActionHelperNotAnObjectException extends CrudEngineRuntimeException
{
    public function __construct(
        private readonly ActionDefinition $actionDefinition,
    ) {
        parent::__construct(
            message: sprintf(
                         'Retrieved Action Helper for Entity %s and Action %s is not an object.',
                         $this->actionDefinition->getEntityAction()->getEntityClass(),
                         $this->actionDefinition->getEntityAction()->getAction(),
                     ),
        );
    }

    public function getActionDefinition(): ActionDefinition
    {
        return $this->actionDefinition;
    }
}
