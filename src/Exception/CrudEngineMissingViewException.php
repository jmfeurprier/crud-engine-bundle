<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Configuration\ActionConfiguration;

class CrudEngineMissingViewException extends CrudEngineRuntimeException
{
    public function __construct(
        private readonly ActionConfiguration $actionConfiguration,
        private readonly string $viewPath,
    ) {
        parent::__construct(
            sprintf(
                'No view template "%s" found for class %s and action "%s".',
                $this->viewPath,
                $this->actionConfiguration->getEntityAction()->getEntityClass(),
                $this->actionConfiguration->getEntityAction()->getAction(),
            ),
        );
    }

    public function getActionConfiguration(): ActionConfiguration
    {
        return $this->actionConfiguration;
    }

    public function getViewPath(): string
    {
        return $this->viewPath;
    }
}
