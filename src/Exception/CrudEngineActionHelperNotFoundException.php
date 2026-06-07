<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Model\ActionConfiguration;

class CrudEngineActionHelperNotFoundException extends CrudEngineRuntimeException
{
    /**
     * @param class-string $helperClass
     */
    public function __construct(
        private readonly ActionConfiguration $actionConfiguration,
        private readonly string $helperClass,
    ) {
        parent::__construct(
            message: sprintf(
                         'Action Helper %s for Entity %s and Action %s not found.',
                         $this->helperClass,
                         $this->actionConfiguration->getEntityAction()->getEntityClass(),
                         $this->actionConfiguration->getEntityAction()->getAction(),
                     ),
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
