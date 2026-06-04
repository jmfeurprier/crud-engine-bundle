<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Resolution;

use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type ResolvedRedirection array{route: non-empty-string, parameters: array<string, string>, fragment: string|null}
 */
readonly class RedirectionConfigurationResolver
{
    /**
     * @var array<non-empty-string, array{route: non-empty-string, parameters: array<string, non-empty-string>}>
     */
    private const array DEFAULT_REDIRECTIONS = [
        'create' => [
            'route'      => "{{ entity_key }}.read",
            'parameters' => ['id' => '{{ _entity.id }}'],
        ],
        'delete' => [
            'route'      => "{{ entity_key }}.index",
            'parameters' => [],
        ],
        'update' => [
            'route'      => "{{ entity_key }}.read",
            'parameters' => ['id' => '{{ _entity.id }}'],
        ],
    ];

    public function __construct(
        private ConfigurationValueResolver $configurationValueResolver,
        private MapResolver $mapResolver,
    ) {
    }

    /**
     * @param array<string, mixed>                      $schema
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $actionConfig
     *
     * @return ResolvedRedirection|null
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    public function resolve(
        array $schema,
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?array {
        if (array_key_exists('redirection', $actionConfig)) {
            $redirectionConfig = $actionConfig['redirection'];
            Assert::isMap($redirectionConfig);

            if (!array_key_exists('route', $redirectionConfig)) {
                throw new CrudEngineMissingConfigurationException($entityClass, $action, 'redirection.route');
            }

            Assert::stringNotEmpty($redirectionConfig['route']);

            return [
                'route'      => $redirectionConfig['route'],
                'parameters' => $this->getStringMap($redirectionConfig, 'parameters'),
                'fragment'   => $this->getNullableString($redirectionConfig, 'fragment'),
            ];
        }

        /** @var array<non-empty-string, array{route: non-empty-string, parameters: array<string, non-empty-string>}> $redirections */
        $redirections = array_merge(
            self::DEFAULT_REDIRECTIONS,
            $this->mapResolver->resolve(
                $schema,
                'redirection',
            ),
        );

        if (!array_key_exists($action, $redirections)) {
            return null;
        }

        $schemaRedirection = $redirections[$action];
        Assert::isMap($schemaRedirection);
        Assert::keyExists($schemaRedirection, 'route');
        Assert::stringNotEmpty($schemaRedirection['route']);

        $route = $this->configurationValueResolver->resolve(
            $schemaRedirection['route'],
            $keys,
            $entityClass,
            $action,
        );
        Assert::stringNotEmpty($route);

        return [
            'route'      => $route,
            'parameters' => $this->getStringMap($schemaRedirection, 'parameters'),
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
