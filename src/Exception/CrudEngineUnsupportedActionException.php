<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Model\EntityAction;

class CrudEngineUnsupportedActionException extends CrudEngineRuntimeException
{
    public function __construct(
        private readonly EntityAction $entityAction,
    ) {
        parent::__construct(
            message: sprintf(
                         'Unsupported CRUD action "%s" for entity class %s.',
                         $this->entityAction->getAction()->value,
                         $this->entityAction->getEntityClass(),
                     ),
        );
    }

    public function getEntityAction(): EntityAction
    {
        return $this->entityAction;
    }
}
