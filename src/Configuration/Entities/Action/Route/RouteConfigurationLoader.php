<?php

namespace Jmf\CrudEngine\Configuration\Entities\Action\Route;

use Jmf\CrudEngine\Configuration\KeyStringCollection;
use Jmf\CrudEngine\Configuration\Schema\SchemaConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Symfony\Component\String\Inflector\InflectorInterface;
use Throwable;
use Webmozart\Assert\Assert;
use function Symfony\Component\String\u;

readonly class RouteConfigurationLoader
{
    public function __construct(
        private TemplateRendererInterface $templateRenderer,
        private InflectorInterface $inflector,
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
            $this->getPath($entityClass, $action, $routeConfig),
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
