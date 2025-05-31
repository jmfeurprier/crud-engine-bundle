<?php

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Configuration\ActionConfiguration;

class CrudEngineUnsupportedActionException extends CrudEngineException
{
    public function __construct(
        private readonly ActionConfiguration $actionConfiguration,
    ) {
        parent::__construct(
            message: vsprintf(
                         'Unsupported CRUD action "%s" for entity class %s.',
                         [
                             $this->actionConfiguration->getAction(),
                             $this->actionConfiguration->getEntityClass(),
                         ],
                     ),
        );
    }

    public function getActionConfiguration(): ActionConfiguration
    {
        return $this->actionConfiguration;
    }
}
