<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\Redirection;

use Jmf\CrudEngine\Configuration\Schema\Redirection\RedirectionSchema;
use Jmf\CrudEngine\Configuration\Schema\Redirection\Route\RedirectionRouteSchema;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Webmozart\Assert\Assert;

readonly class ActionRedirectionConfigurationLoader
{
    public function __construct(
        private SchemaValueExpander $schemaValueExpander,
    ) {
    }

    /**
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $actionConfig
     *
     * @throws CrudEngineConfigurationException
     */
    public function load(
        RedirectionSchema $redirectionSchema,
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?ActionRedirectionConfiguration {
        if (!array_key_exists('redirection', $actionConfig)) {
            return $this->tryGetFallback(
                $redirectionSchema,
                $keys,
                $entityClass,
                $action,
            );
        }

        Assert::isMap($actionConfig['redirection']);

        $redirectionConfig = $actionConfig['redirection'];

        return new ActionRedirectionConfiguration(
            $this->getRoute($entityClass, $action, $redirectionConfig),
            $this->getParameters($redirectionConfig),
            $this->getFragment($redirectionConfig),
        );
    }

    /**
     * @param array<non-empty-string, non-empty-string> $keys
     * @param non-empty-string                          $action
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function tryGetFallback(
        RedirectionSchema $redirectionSchema,
        array $keys,
        string $entityClass,
        string $action,
    ): ?ActionRedirectionConfiguration {
        $redirectionRouteSchema = $redirectionSchema->tryGet($action);

        if (!$redirectionRouteSchema instanceof RedirectionRouteSchema) {
            return null;
        }

        $route = $this->schemaValueExpander->expand(
            $redirectionRouteSchema->getRoute(),
            $keys,
            [
                'entityClass' => $entityClass,
                'action'      => $action,
            ],
        );

        $parameters = $redirectionRouteSchema->getParameters();

        return new ActionRedirectionConfiguration(
            $route,
            new ActionRedirectionParameterCollection($parameters),
        );
    }

    /**
     * @param class-string         $entityClass
     * @param array<string, mixed> $redirectionConfig
     *
     * @throws CrudEngineMissingConfigurationException
     */
    private function getRoute(
        string $entityClass,
        string $action,
        array $redirectionConfig,
    ): string {
        if (!array_key_exists('route', $redirectionConfig)) {
            throw new CrudEngineMissingConfigurationException(
                $entityClass,
                $action,
                'redirection.route',
            );
        }

        Assert::string($redirectionConfig['route']);

        return $redirectionConfig['route'];
    }

    /**
     * @param array<string, mixed> $redirectionConfig
     */
    private function getParameters(
        array $redirectionConfig,
    ): ActionRedirectionParameterCollection {
        if (!array_key_exists('parameters', $redirectionConfig)) {
            return ActionRedirectionParameterCollection::createDefault();
        }

        $parametersConfig = $redirectionConfig['parameters'];

        Assert::isMap($parametersConfig);
        Assert::allString($parametersConfig);

        return new ActionRedirectionParameterCollection($parametersConfig);
    }

    /**
     * @param array<string, mixed> $redirectionConfig
     */
    private function getFragment(
        array $redirectionConfig,
    ): ?string {
        if (!array_key_exists('fragment', $redirectionConfig)) {
            return null;
        }

        Assert::string($redirectionConfig['fragment']);

        return $redirectionConfig['fragment'];
    }
}
