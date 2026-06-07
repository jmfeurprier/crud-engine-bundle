<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Throwable;

class CrudEngineViewRenderingException extends CrudEngineRuntimeException
{
    public function __construct(
        private readonly ActionDefinition $actionDefinition,
        ?Throwable $previousException = null,
    ) {
        parent::__construct(
            message:  sprintf(
                          'Failed rendering CRUD view for class %s and action "%s".',
                          $this->actionDefinition->getEntityAction()->getEntityClass(),
                          $this->actionDefinition->getEntityAction()->getAction(),
                      ),
            previous: $previousException,
        );
    }

    public function getActionDefinition(): ActionDefinition
    {
        return $this->actionDefinition;
    }
}
