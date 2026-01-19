<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Throwable;

class CrudEngineRedirectionParameterRenderingException extends CrudEngineRuntimeException
{
    public function __construct(
        private readonly ActionConfiguration $actionConfiguration,
        private readonly string $key,
        private readonly string $definition,
        ?Throwable $previousException = null,
    ) {
        parent::__construct(
            message:  sprintf(
                          'Failed rendering CRUD redirection parameter "%s" (definition: "%s") "
                          . "for entity class %s and action "%s".',
                          $this->key,
                          $this->definition,
                          $this->actionConfiguration->getEntityClass(),
                          $this->actionConfiguration->getAction(),
                      ),
            previous: $previousException,
        );
    }

    public function getActionConfiguration(): ActionConfiguration
    {
        return $this->actionConfiguration;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getDefinition(): string
    {
        return $this->definition;
    }
}
