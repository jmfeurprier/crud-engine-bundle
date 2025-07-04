<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Jmf\CrudEngine\Configuration\Action\ActionConfiguration;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperInterface;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;

/**
 * @template T of ActionHelperInterface
 */
trait WithActionHelperTrait
{
    private readonly ActionHelperResolver $actionHelperResolver;

    /**
     * @param class-string<T> $class
     *
     * @psalm-return T
     *
     * @throws CrudEngineInvalidActionHelperException
     */
    private function getActionHelper(
        string $class,
        ActionConfiguration $actionConfiguration,
        ActionHelperInterface $defaultActionHelper,
    ): ActionHelperInterface {
        return $this->actionHelperResolver->resolve(
            $class,
            $actionConfiguration,
            $defaultActionHelper,
        );
    }
}
