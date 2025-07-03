<?php

namespace Jmf\CrudEngine\Configuration\Action\Route;

use Jmf\CrudEngine\Configuration\KeyStringCollection;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Symfony\Component\String\Inflector\InflectorInterface;
use Webmozart\Assert\Assert;
use function Symfony\Component\String\u;

readonly class RouteConfigurationLoader
{
    public function __construct(
        private InflectorInterface $inflector,
    ) {
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @throws CrudEngineMissingConfigurationException
     */
    public function load(
        string $entityClass,
        string $action,
        array $actionConfig,
    ): RouteConfiguration {
        if (!array_key_exists('route', $actionConfig)) {
            return $this->getFallbackRouteConfiguration($entityClass, $action);
        }

        $routeConfig = $actionConfig['route'];

        Assert::isMap($routeConfig);

        return new RouteConfiguration(
            $this->getName($routeConfig),
            $this->getPath($entityClass, $action, $routeConfig),
            $this->getRequirements($routeConfig),
        );
    }

    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     *
     * @throws CrudEngineMissingConfigurationException
     */
    private function getFallbackRouteConfiguration(
        string $entityClass,
        string $action,
    ): RouteConfiguration {
        $path = $this->tryGetFallbackPath($entityClass, $action);

        if (null === $path) {
            throw new CrudEngineMissingConfigurationException(
                $entityClass,
                $action,
                'route.path',
            );
        }

        return new RouteConfiguration(
            null,
            $path,
            KeyStringCollection::createEmpty(),
        );
    }

    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     */
    public function tryGetFallbackPath(
        string $entityClass,
        string $action,
    ): ?string {
        $token  = u($entityClass)->afterLast('\\');
        $tokens = $this->inflector->pluralize($token);

        if (1 !== count($tokens)) {
            return null;
        }

        $token = $tokens[0];
        $token = u($token)->kebab()->toString();

        // @todo Externalize logic.
        return match ($action) {
            'create' => "{$token}/create",
            'delete' => "{$token}/{id}/delete",
            'index' => "{$token}",
            'read' => "{$token}/{id}",
            'update' => "{$token}/{id}/update",
            default => null,
        };
    }

    /**
     * @param array<string, mixed> $routeConfig
     *
     * @return null|non-empty-string
     */
    private function getName(array $routeConfig): ?string
    {
        if (!array_key_exists('name', $routeConfig)) {
            // @todo Generate from entity class.

            return null;
        }

        Assert::stringNotEmpty($routeConfig['name']);

        return $routeConfig['name'];
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $routeConfig
     *
     * @throws CrudEngineMissingConfigurationException
     */
    private function getPath(
        string $entityClass,
        string $action,
        array $routeConfig,
    ): string {
        if (!array_key_exists('path', $routeConfig)) {
            return $this->tryGetFallbackPath(
                $entityClass,
                $action,
            )
                ??
                throw new CrudEngineMissingConfigurationException(
                    $entityClass,
                    $action,
                    'route.path',
                );
        }

        Assert::stringNotEmpty($routeConfig['path']);

        return $routeConfig['path'];
    }

    /**
     * @param array<string, mixed> $routeConfig
     */
    private function getRequirements(
        array $routeConfig,
    ): KeyStringCollection {
        if (!array_key_exists('requirements', $routeConfig)) {
            return KeyStringCollection::createEmpty();
        }

        $requirementsConfig = $routeConfig['requirements'];

        Assert::isMap($requirementsConfig);
        Assert::allString($requirementsConfig);

        return new KeyStringCollection($requirementsConfig);
    }
}
