<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Model\ActionConfiguration;
use Jmf\CrudEngine\Model\EntityAction;

class CrudEngineUnsupportedActionException extends CrudEngineRuntimeException
{
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

    public static function forActionConfiguration(
        ActionConfiguration $actionConfiguration,
    ): self {
        return new self(
            $actionConfiguration->getEntityAction(),
            $actionConfiguration,
        );
    }

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

    public function getEntityAction(): EntityAction
    {
        return $this->entityAction;
    }

    public function getActionConfiguration(): ?ActionConfiguration
    {
        return $this->actionConfiguration;
    }
}
