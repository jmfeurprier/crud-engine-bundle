<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\Redirection;

use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Webmozart\Assert\Assert;

// @todo Use schema.
readonly class ActionRedirectionConfigurationLoader
{
    /**
     * @param class-string         $entityClass
     * @param array<string, mixed> $actionConfig
     *
     * @throws CrudEngineMissingConfigurationException
     */
    public function load(
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?ActionRedirectionConfiguration {
        if (!array_key_exists('redirection', $actionConfig)) {
            return null;
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
    private function getFragment(array $redirectionConfig): ?string
    {
        if (!array_key_exists('fragment', $redirectionConfig)) {
            return null;
        }

        Assert::string($redirectionConfig['fragment']);

        return $redirectionConfig['fragment'];
    }
}
