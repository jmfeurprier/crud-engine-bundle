<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperInterface;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;

/**
 * @template T of ActionHelperInterface
 */
trait WithActionHelperTrait
{
    private ActionHelperResolver $actionHelperResolver;

    /**
     * @psalm-var T
     */
    private ActionHelperInterface $defaultActionHelper;

    /**
     * @param class-string<T> $class
     *
     * @return T
     *
     * @throws CrudEngineInvalidActionHelperException
     */
    private function getActionHelper(
        string $class,
        ActionConfiguration $actionConfiguration,
    ): ActionHelperInterface {
        return $this->actionHelperResolver->resolve(
            $class,
            $actionConfiguration,
            $this->defaultActionHelper,
        );
    }
}
