<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Model\ActionConfiguration;
use Jmf\CrudEngine\Model\EntityAction;

// @todo Refactor / Split.
class CrudEngineUnsupportedActionException extends CrudEngineRuntimeException
{
    // @xxx
    public static function forActionConfiguration(
        ActionConfiguration $actionConfiguration,
    ): self {
        return new self(
            $actionConfiguration->getEntityAction(),
            $actionConfiguration,
        );
    }

    // @xxx
    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     */
    public static function forAction(
        string $entityClass,
        string $action,
    ): self {
        return new self(
            new EntityAction($entityClass, $action),
        );
    }

    private function __construct(
        private readonly EntityAction $entityAction,
        private readonly ?ActionConfiguration $actionConfiguration = null,
    ) {
        parent::__construct(
            message: sprintf(
                         'Unsupported CRUD action "%s" for entity class %s.',
                         $this->entityAction->getAction(),
                         $this->entityAction->getEntityClass(),
                     ),
        );
    }

    public function getEntityAction(): EntityAction
    {
        return $this->entityAction;
    }

    public function getActionConfiguration(): ?ActionConfiguration
    {
        return $this->actionConfiguration;
    }
}
