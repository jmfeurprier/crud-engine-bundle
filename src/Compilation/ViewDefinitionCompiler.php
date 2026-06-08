<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation;

use Jmf\CrudEngine\Compilation\Resolution\ConfigurationValueResolver;
use Jmf\CrudEngine\Compilation\Resolution\MapResolver;
use Jmf\CrudEngine\Compilation\Resolution\OverridableConfigurationValueResolver;
use Jmf\CrudEngine\Definition\FallbackMode;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Model\CrudAction;
use Jmf\CrudEngine\Model\EntityAction;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type CompiledView array{path: non-empty-string, variables: array<non-empty-string, list<non-empty-string>>, fallback: non-empty-string}
 */
readonly class ViewDefinitionCompiler
{
    /**
     * @var non-empty-string
     */
    private const string DEFAULT_PATH = "{{ entity_key }}/{{ action_key }}.html.twig";

    public function __construct(
        private ConfigurationValueResolver $configurationValueResolver,
        private MapResolver $mapResolver,
        private OverridableConfigurationValueResolver $overridableConfigurationValueResolver,
    ) {
    }

    /**
     * @param array<string, mixed>                      $schema
     * @param array<non-empty-string, non-empty-string> $keys
     * @param array<string, mixed>                      $actionConfig
     *
     * @return CompiledView
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function compile(
        array $schema,
        array $keys,
        EntityAction $entityAction,
        array $actionConfig,
    ): array {
        $viewConfig = $this->mapResolver->resolve($actionConfig, 'view');
        $schemaView = $this->mapResolver->resolve($schema, 'view');

        return [
            'path'      => $this->overridableConfigurationValueResolver->resolve(
                $viewConfig,
                'path',
                $schemaView,
                'path',
                self::DEFAULT_PATH,
                $keys,
                $entityAction,
            ),
            'variables' => $this->resolveVariables(
                $schemaView,
                $viewConfig,
                $keys,
                $entityAction,
            ),
            'fallback'  => $this->resolveFallback($schemaView),
        ];
    }

    /**
     * @param array<string, mixed>                      $schemaView
     * @param array<string, mixed>                      $viewConfig
     * @param array<non-empty-string, non-empty-string> $keys
     *
     * @return array<non-empty-string, list<non-empty-string>>
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function resolveVariables(
        array $schemaView,
        array $viewConfig,
        array $keys,
        EntityAction $entityAction,
    ): array {
        $variables = [];

        foreach ($this->mapResolver->resolve($schemaView, 'variables') as $name => $values) {
            Assert::stringNotEmpty($name);

            $expanded = [];

            foreach ($this->toList($values) as $value) {
                $value = $this->configurationValueResolver->resolve(
                    $value,
                    $keys,
                    $entityAction,
                );

                Assert::stringNotEmpty($value);

                $expanded[] = $value;
            }

            $variables[$name] = $expanded;
        }

        foreach ($this->mapResolver->resolve($viewConfig, 'variables') as $name => $values) {
            Assert::stringNotEmpty($name);

            $variables[$name] = $this->toList($values);
        }

        return $variables;
    }

    /**
     * @param array<string, mixed> $schemaView
     *
     * @return non-empty-string
     */
    private function resolveFallback(array $schemaView): string
    {
        if (!array_key_exists('fallback', $schemaView)) {
            return FallbackMode::PROVIDE->value;
        }

        Assert::stringNotEmpty($schemaView['fallback']);

        $fallback = FallbackMode::tryFrom($schemaView['fallback']);

        Assert::notNull(
            $fallback,
            sprintf('Unknown view fallback mode "%s".', $schemaView['fallback']),
        );

        return $fallback->value;
    }

    /**
     * @return list<non-empty-string>
     */
    private function toList(mixed $values): array
    {
        if (!is_iterable($values)) {
            $values = [$values];
        }

        $list = [];

        foreach ($values as $value) {
            Assert::stringNotEmpty($value);

            $list[] = $value;
        }

        return $list;
    }
}
