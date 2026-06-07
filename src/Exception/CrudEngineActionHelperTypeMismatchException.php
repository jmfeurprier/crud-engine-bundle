<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Controller\Helpers\ActionHelperInterface;
use Jmf\CrudEngine\Model\ActionConfiguration;

class CrudEngineActionHelperTypeMismatchException extends CrudEngineRuntimeException
{
    /**
     * @param class-string<ActionHelperInterface> $expectedClass
     */
    public function __construct(
        private readonly ActionConfiguration $actionConfiguration,
        private readonly string $actualClass,
        private readonly string $expectedClass,
    ) {
        parent::__construct(
            message: sprintf(
                         'Action Helper %s for Entity %s and Action %s does not implement/extend %s',
                         $this->actualClass,
                         $this->actionConfiguration->getEntityAction()->getEntityClass(),
                         $this->actionConfiguration->getEntityAction()->getAction(),
                         $this->expectedClass,
                     ),
        );
    }

    public function getActionConfiguration(): ActionConfiguration
    {
        return $this->actionConfiguration;
    }

    public function getActualClass(): string
    {
        return $this->actualClass;
    }

    public function getExpectedClass(): string
    {
        return $this->expectedClass;
    }
}
