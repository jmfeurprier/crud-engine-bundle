<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Definition\ActionDefinition;

class CrudEngineMissingViewException extends CrudEngineRuntimeException
{
    public function __construct(
        private readonly ActionDefinition $actionDefinition,
        private readonly string $viewPath,
    ) {
        parent::__construct(
            sprintf(
                'No view template "%s" found for class %s and action "%s".',
                $this->viewPath,
                $this->actionDefinition->getEntityAction()->getEntityClass(),
                $this->actionDefinition->getEntityAction()->getAction(),
            ),
        );
    }

    public function getActionDefinition(): ActionDefinition
    {
        return $this->actionDefinition;
    }

    public function getViewPath(): string
    {
        return $this->viewPath;
    }
}
