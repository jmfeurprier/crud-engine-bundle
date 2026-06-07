<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Throwable;

class CrudEngineActionHelperRetrievalException extends CrudEngineRuntimeException
{
    /**
     * @param class-string $helperClass
     */
    public function __construct(
        private readonly ActionConfiguration $actionConfiguration,
        private readonly string $helperClass,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message:  sprintf(
                          'Failed retrieving Action Helper %s for Entity %s and Action %s from container.',
                          $this->helperClass,
                          $this->actionConfiguration->getEntityAction()->getEntityClass(),
                          $this->actionConfiguration->getEntityAction()->getAction(),
                      ),
            previous: $previous,
        );
    }

    public function getActionConfiguration(): ActionConfiguration
    {
        return $this->actionConfiguration;
    }

    public function getHelperClass(): string
    {
        return $this->helperClass;
    }
}
