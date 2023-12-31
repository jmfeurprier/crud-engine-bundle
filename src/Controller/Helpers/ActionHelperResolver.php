<?php

namespace Jmf\CrudEngine\Controller\Helpers;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Symfony\Component\DependencyInjection\ContainerInterface;

readonly class ActionHelperResolver
{
    public function __construct(
        private ContainerInterface $container,
    ) {
    }

    /**
     * @template T of ActionHelperInterface
     *
     * @param class-string<T> $class
     *
     * @psalm-return T
     *
     * @throws CrudEngineInvalidActionHelperException
     */
    public function resolve(
        string $class,
        ActionConfiguration $actionConfiguration,
        ActionHelperInterface $defaultActionHelper,
    ): ActionHelperInterface {
        $actionHelper = $defaultActionHelper;

        if (null !== $actionConfiguration->getHelperClass()) {
            $helperClass = $actionConfiguration->getHelperClass();

            if (!$this->container->has($helperClass)) {
                throw new CrudEngineInvalidActionHelperException();
            }

            $actionHelper = $this->container->get($helperClass);
        }

        if ($actionHelper instanceof $class) {
            return $actionHelper;
        }

        throw new CrudEngineInvalidActionHelperException();
    }
}
