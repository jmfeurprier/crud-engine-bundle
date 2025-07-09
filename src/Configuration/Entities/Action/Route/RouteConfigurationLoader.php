<?php

namespace Jmf\CrudEngine\Configuration\Entities\Action\Route;

use Jmf\CrudEngine\Configuration\KeyStringCollection;
use Jmf\CrudEngine\Configuration\Schema\SchemaConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Throwable;
use Webmozart\Assert\Assert;

readonly class RouteConfigurationLoader
{
    public function __construct(
        private TemplateRendererInterface $templateRenderer,
    ) {
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    public function load(
        SchemaConfiguration $schemaConfiguration,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): RouteConfiguration {
        $routeConfig = $this->getRouteConfig($actionConfig);

        return new RouteConfiguration(
            $this->getName($schemaConfiguration, $entityClass, $action, $routeConfig),
            $this->getPath($schemaConfiguration, $entityClass, $action, $routeConfig),
            $this->getRequirements($routeConfig),
        );
    }

    /**
     * @param array<string, mixed> $actionConfig
     *
     * @return array<string, mixed>
     */
    private function getRouteConfig(array $actionConfig): array
    {
        if (!array_key_exists('route', $actionConfig)) {
            return [];
        }

        $routeConfig = $actionConfig['route'];

        Assert::isMap($routeConfig);

        return $routeConfig;
    }

    /**
     * @param class-string         $entityClass
     * @param array<string, mixed> $routeConfig
     * @param non-empty-string     $action
     *
     * @return non-empty-string
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function getName(
        SchemaConfiguration $schemaConfiguration,
        string $entityClass,
        string $action,
        array $routeConfig,
    ): string {
        if (!array_key_exists('name', $routeConfig)) {
            return $this->getFallbackName($schemaConfiguration, $entityClass, $action);
        }

        Assert::stringNotEmpty($routeConfig['name']);

        return $routeConfig['name'];
    }

    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     *
     * @return non-empty-string
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function getFallbackName(
        SchemaConfiguration $schemaConfiguration,
        string $entityClass,
        string $action,
    ): string {
        try {
            $name = $this->templateRenderer->renderFromString(
                $schemaConfiguration->getRouteConfiguration()->getName(),
                [
                    'entityClass' => $entityClass,
                    'action'      => $action,
                ],
            );
        } catch (Throwable $e) {
            // @todo Add mode context.
            throw new CrudEngineInvalidConfigurationException(
                previous: $e,
            );
        }

        Assert::stringNotEmpty($name);

        return $name;
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $routeConfig
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    private function getPath(
        SchemaConfiguration $schemaConfiguration,
        string $entityClass,
        string $action,
        array $routeConfig,
    ): string {
        if (array_key_exists('path', $routeConfig)) {
            $path = $routeConfig['path'];

            Assert::string($path);

            return $path;
        }

        return $this->tryGetFallbackPath(
            $schemaConfiguration,
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

    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function tryGetFallbackPath(
        SchemaConfiguration $schemaConfiguration,
        string $entityClass,
        string $action,
    ): ?string {
        $path = $schemaConfiguration->getRouteConfiguration()->getPaths()->tryGet($action);

        if (null === $path) {
            return null;
        }

        try {
            return $this->templateRenderer->renderFromString(
                $path,
                [
                    'entityClass' => $entityClass,
                    'action'      => $action,
                ],
            );
        } catch (Throwable $e) {
            // @todo Add mode context.
            throw new CrudEngineInvalidConfigurationException(
                previous: $e,
            );
        }
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
