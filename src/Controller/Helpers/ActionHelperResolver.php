<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller\Helpers;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
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
     * @psalm-param class-string<T> $class
     *
     * @psalm-return T
     *
     * @throws CrudEngineActionHelperNotAnObjectException
     * @throws CrudEngineActionHelperNotFoundException
     * @throws CrudEngineActionHelperRetrievalException
     * @throws CrudEngineActionHelperTypeMismatchException
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
            } catch (NotFoundExceptionInterface) {
                throw new CrudEngineActionHelperNotFoundException(
                    $actionConfiguration,
                    $helperClass,
                );
            } catch (ContainerExceptionInterface $e) {
                throw new CrudEngineActionHelperRetrievalException(
                    $actionConfiguration,
                    $helperClass,
                    $e,
                );
            }
        }

        if (!is_object($actionHelper)) {
            throw new CrudEngineActionHelperNotAnObjectException($actionConfiguration);
        }

        if (!$actionHelper instanceof $class) {
            throw new CrudEngineActionHelperTypeMismatchException(
                $actionConfiguration,
                $actionHelper::class,
                $class,
            );
        }

        return $actionHelper;
    }
}
