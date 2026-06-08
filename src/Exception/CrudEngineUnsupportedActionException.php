<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Model\EntityAction;

// @todo Refactor / Split.
class CrudEngineUnsupportedActionException extends CrudEngineRuntimeException
{
    // @xxx
    public static function forActionDefinition(
        ActionDefinition $actionDefinition,
    ): self {
        return new self(
            $actionDefinition->getEntityAction(),
            $actionDefinition,
        );
    }

    // @xxx
    public function __construct(
        private readonly EntityAction $entityAction,
        private readonly ?ActionDefinition $actionDefinition = null,
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

    public function getActionDefinition(): ?ActionDefinition
    {
        return $this->actionDefinition;
    }
}
