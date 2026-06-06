<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller\Helpers;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
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
     * @throws CrudEngineActionHelperNotFoundException
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

            try {
                $actionHelper = $this->container->get($helperClass);
            } catch (NotFoundExceptionInterface $e) {
                throw new CrudEngineActionHelperNotFoundException(
                    $actionConfiguration,
                    $helperClass,
                );
            } catch (ContainerExceptionInterface $e) {
                throw new CrudEngineInvalidActionHelperException(
                    message:  sprintf(
                                  'Failed retrieving Action Helper %s for Entity %s and Action %s from container.',
                                  $helperClass,
                                  $actionConfiguration->getEntityAction()->getEntityClass(),
                                  $actionConfiguration->getEntityAction()->getAction(),
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
                    $actionConfiguration->getEntityAction()->getEntityClass(),
                    $actionConfiguration->getEntityAction()->getAction(),
                ),
            );
        }

        if (!$actionHelper instanceof $class) {
            throw new CrudEngineInvalidActionHelperException(
                sprintf(
                    'Action Helper %s for Entity %s and Action %s does not implement/extend %s',
                    $actionHelper::class,
                    $actionConfiguration->getEntityAction()->getEntityClass(),
                    $actionConfiguration->getEntityAction()->getAction(),
                    $class,
                ),
            );
        }

        return $actionHelper;
    }
}
