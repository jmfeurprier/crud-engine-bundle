<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Configuration\ActionConfiguration;

class CrudEngineActionHelperNotAnObjectException extends CrudEngineRuntimeException
{
    public function __construct(
        private readonly ActionConfiguration $actionConfiguration,
    ) {
        parent::__construct(
            message: sprintf(
                         'Retrieved Action Helper for Entity %s and Action %s is not an object.',
                         $this->actionConfiguration->getEntityAction()->getEntityClass(),
                         $this->actionConfiguration->getEntityAction()->getAction(),
                     ),
        );
    }

    public function getActionConfiguration(): ActionConfiguration
    {
        return $this->actionConfiguration;
    }
}
