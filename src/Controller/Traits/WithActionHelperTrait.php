<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Jmf\CrudEngine\Controller\ActionHelperInterface;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @template T of ActionHelperInterface
 */
trait WithActionHelperTrait
{
    private ContainerInterface $container;

    /**
     * @var T
     */
    private $defaultActionHelper;

    /**
     * @param class-string<T>      $class
     * @param array<string, mixed> $actionProperties
     *
     * @return T
     *
     * @throws CrudEngineInvalidActionHelperException
     * @throws CrudEngineInvalidConfigurationException
     */
    private function getActionHelper(
        string $class,
        array $actionProperties
    ) {
        $actionHelper = $this->defaultActionHelper;

        if (array_key_exists('helperClass', $actionProperties)) {
            $helperClass = $actionProperties['helperClass'];

            if (!is_string($helperClass)) {
                throw new CrudEngineInvalidConfigurationException();
            }

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
