<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation;

use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type CompiledRedirection array{route: non-empty-string, parameters: array<string, string>, fragment: string|null}
 */
readonly class RedirectionDefinitionCompiler
{
    public function __construct(
        private ConfigurationValueResolver $configurationValueResolver,
        private MapResolver $mapResolver,
    ) {
    }

    /**
     * @param array<string, mixed>                                                        $schema
     * @param array<non-empty-string, non-empty-string>                                   $keys
     * @param class-string                                                                $entityClass
     * @param non-empty-string                                                            $action
     * @param array<string, mixed>                                                        $actionConfig
     * @param array{route: non-empty-string, parameters: array<string, non-empty-string>} $defaultRedirection
     *
     * @return CompiledRedirection
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    public function compile(
        array $schema,
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
        array $defaultRedirection,
    ): array {
        if (array_key_exists('redirection', $actionConfig)) {
            $redirectionConfig = $actionConfig['redirection'];
            Assert::isMap($redirectionConfig);

            if (!array_key_exists('route', $redirectionConfig)) {
                throw new CrudEngineMissingConfigurationException(
                    $entityClass,
                    $action,
                    'redirection.route',
                );
            }

            Assert::stringNotEmpty($redirectionConfig['route']);

            return [
                'route'      => $redirectionConfig['route'],
                'parameters' => $this->getStringMap($redirectionConfig, 'parameters'),
                'fragment'   => $this->getNullableString($redirectionConfig, 'fragment'),
            ];
        }

        $schemaRedirections = $this->mapResolver->resolve($schema, 'redirection');

        $redirection = array_key_exists($action, $schemaRedirections)
            ? $schemaRedirections[$action]
            : $defaultRedirection;

        Assert::isMap($redirection);
        Assert::keyExists($redirection, 'route');
        Assert::stringNotEmpty($redirection['route']);

        $route = $this->configurationValueResolver->resolve(
            $redirection['route'],
            $keys,
            $entityClass,
            $action,
        );
        Assert::stringNotEmpty($route);

        return [
            'route'      => $route,
            'parameters' => $this->getStringMap($redirection, 'parameters'),
            'fragment'   => null,
        ];
    }

    /**
     * @param array<array-key, mixed> $config
     * @param non-empty-string        $key
     *
     * @return array<string, string>
     */
    private function getStringMap(
        array $config,
        string $key,
    ): array {
        if (!array_key_exists($key, $config)) {
            return [];
        }

        $value = $config[$key];
        Assert::isMap($value);

        $result = [];

        foreach ($value as $k => $v) {
            Assert::string($k);
            Assert::string($v);

            $result[$k] = $v;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $config
     * @param non-empty-string     $key
     */
    private function getNullableString(
        array $config,
        string $key,
    ): ?string {
        if (!array_key_exists($key, $config) || null === $config[$key]) {
            return null;
        }

        Assert::string($config[$key]);

        return $config[$key];
    }
}
