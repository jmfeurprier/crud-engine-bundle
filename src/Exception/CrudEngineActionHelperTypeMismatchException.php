<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Controller\Helpers\ActionHelperInterface;
use Jmf\CrudEngine\Definition\ActionDefinition;

class CrudEngineActionHelperTypeMismatchException extends CrudEngineRuntimeException
{
    /**
     * @param class-string<ActionHelperInterface> $expectedClass
     */
    public function __construct(
        private readonly ActionDefinition $actionDefinition,
        private readonly string $actualClass,
        private readonly string $expectedClass,
    ) {
        parent::__construct(
            message: sprintf(
                         'Action Helper %s for Entity %s and Action %s does not implement/extend %s',
                         $this->actualClass,
                         $this->actionDefinition->getEntityAction()->getEntityClass(),
                         $this->actionDefinition->getEntityAction()->getAction(),
                         $this->expectedClass,
                     ),
        );
    }

    public function getActionDefinition(): ActionDefinition
    {
        return $this->actionDefinition;
    }

    public function getActualClass(): string
    {
        return $this->actualClass;
    }

    public function getExpectedClass(): string
    {
        return $this->expectedClass;
    }
}
