<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller\Helpers;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

readonly class ActionHelperResolver
{
    public function __construct(
        #[AutowireLocator('jmf_crud_engine.action_helper')]
        private ContainerInterface $container,
    ) {
    }

    /**
     * @template T of ActionHelperInterface
     *
     * @psalm-param class-string<T> $class
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
                throw new CrudEngineInvalidActionHelperException(
                    sprintf(
                        'Action Helper %s for Entity %s and Action %s not found.',
                        $helperClass,
                        $actionConfiguration->getEntityClass(),
                        $actionConfiguration->getAction(),
                    ),
                );
            }

            try {
                $actionHelper = $this->container->get($helperClass);
            } catch (ContainerExceptionInterface $e) {
                throw new CrudEngineInvalidActionHelperException(
                    message:  sprintf(
                                  'Failed retrieving Action Helper %s for Entity %s and Action %s from container.',
                                  $helperClass,
                                  $actionConfiguration->getEntityClass(),
                                  $actionConfiguration->getAction(),
                              ),
                    code:     $e->getCode(),
                    previous: $e,
                );
            }
        }

        if (!is_object($actionHelper)) {
            throw new CrudEngineInvalidActionHelperException(
                sprintf(
                    'Retrieved Action Helper for Entity %s and Action %s is not an object.',
                    $actionConfiguration->getEntityClass(),
                    $actionConfiguration->getAction(),
                ),
            );
        }

        if (!$actionHelper instanceof $class) {
            throw new CrudEngineInvalidActionHelperException(
                sprintf(
                    'Action Helper %s for Entity %s and Action %s does not implement/extend %s',
                    $actionHelper::class,
                    $actionConfiguration->getEntityClass(),
                    $actionConfiguration->getAction(),
                    $class,
                ),
            );
        }

        return $actionHelper;
    }
}
