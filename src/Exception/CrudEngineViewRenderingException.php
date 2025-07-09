<?php

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Throwable;

class CrudEngineViewRenderingException extends CrudEngineRuntimeException
{
    public function __construct(
        private readonly ActionConfiguration $actionConfiguration,
        ?Throwable $previousException = null,
    ) {
        parent::__construct(
            message:  vsprintf(
                          'Failed rendering CRUD view for class %s and action "%s".',
                          [
                              $this->actionConfiguration->getEntityClass(),
                              $this->actionConfiguration->getAction(),
                          ],
                      ),
            previous: $previousException,
        );
    }

    public function getActionConfiguration(): ActionConfiguration
    {
        return $this->actionConfiguration;
    }
}
