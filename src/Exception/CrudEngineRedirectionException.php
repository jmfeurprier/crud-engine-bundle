<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Throwable;

class CrudEngineRedirectionException extends CrudEngineRuntimeException
{
    public function __construct(
        private readonly ActionConfiguration $actionConfiguration,
        ?Throwable $previousException = null,
    ) {
        parent::__construct(
            message:  sprintf(
                          'Failed generating redirection URL for class %s and action "%s".',
                          $this->actionConfiguration->getEntityAction()->getEntityClass(),
                          $this->actionConfiguration->getEntityAction()->getAction(),
                      ),
            previous: $previousException,
        );
    }

    public function getActionConfiguration(): ActionConfiguration
    {
        return $this->actionConfiguration;
    }
}
