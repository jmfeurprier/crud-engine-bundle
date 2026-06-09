<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller\Helpers;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperNotAnObjectException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperRetrievalException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperTypeMismatchException;
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
     * @param class-string<T> $class
     *
     * @return T
     *
     * @throws CrudEngineActionHelperNotAnObjectException
     * @throws CrudEngineActionHelperNotFoundException
     * @throws CrudEngineActionHelperRetrievalException
     * @throws CrudEngineActionHelperTypeMismatchException
     */
    public function resolve(
        string $class,
        ActionDefinition $actionDefinition,
        ActionHelperInterface $defaultActionHelper,
    ): ActionHelperInterface {
        $actionHelper = $defaultActionHelper;
        $helperClass  = $actionDefinition->getHelperClass();

        if (null !== $helperClass) {
            try {
                $actionHelper = $this->container->get($helperClass);
            } catch (NotFoundExceptionInterface) {
                throw new CrudEngineActionHelperNotFoundException(
                    $actionDefinition,
                    $helperClass,
                );
            } catch (ContainerExceptionInterface $e) {
                throw new CrudEngineActionHelperRetrievalException(
                    $actionDefinition,
                    $helperClass,
                    $e,
                );
            }
        }

        if (!is_object($actionHelper)) {
            throw new CrudEngineActionHelperNotAnObjectException($actionDefinition);
        }

        if (!$actionHelper instanceof $class) {
            throw new CrudEngineActionHelperTypeMismatchException(
                actionDefinition: $actionDefinition,
                actualClass:      $actionHelper::class,
                expectedClass:    $class,
            );
        }

        return $actionHelper;
    }
}
