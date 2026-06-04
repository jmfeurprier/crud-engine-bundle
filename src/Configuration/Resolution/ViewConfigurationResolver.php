<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Resolution;

use Jmf\CrudEngine\Configuration\Entities\Action\View\ViewFallbackMode;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type ResolvedView array{path: non-empty-string, variables: array<non-empty-string, list<non-empty-string>>, fallback: non-empty-string}
 */
readonly class ViewConfigurationResolver
{
    /**
     * @var non-empty-string
     */
    private const string DEFAULT_PATH = "{{ entity_key }}/{{ action_key }}.html.twig";

    public function __construct(
        private ConfigurationValueResolver $configurationValueResolver,
        private MapResolver $mapResolver,
        private OverridableConfigurationValueResolver $overridableConfigurationValueExpander,
    ) {
    }

    /**
     * @param array<string, mixed>                      $schema
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $actionConfig
     *
     * @return ResolvedView
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function resolve(
        array $schema,
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): array {
        $viewConfig = $this->mapResolver->resolve($actionConfig, 'view');
        $schemaView = $this->mapResolver->resolve($schema, 'view');

        return [
            'path'      => $this->overridableConfigurationValueExpander->resolve(
                $viewConfig,
                'path',
                $schemaView,
                'path',
                self::DEFAULT_PATH,
                $keys,
                $entityClass,
                $action,
            ),
            'variables' => $this->resolveVariables($schemaView, $viewConfig, $keys, $entityClass, $action),
            'fallback'  => $this->resolveFallback($schemaView),
        ];
    }

    /**
     * @param array<string, mixed>                      $schemaView
     * @param array<string, mixed>                      $viewConfig
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     *
     * @return array<non-empty-string, list<non-empty-string>>
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function resolveVariables(
        array $schemaView,
        array $viewConfig,
        array $keys,
        string $entityClass,
        string $action,
    ): array {
        $variables = [];

        foreach ($this->mapResolver->resolve($schemaView, 'variables') as $name => $values) {
            Assert::stringNotEmpty($name);

            $expanded = [];

            foreach ($this->toList($values) as $value) {
                $value = $this->configurationValueResolver->resolve(
                    $value,
                    $keys,
                    $entityClass,
                    $action,
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
            return ViewFallbackMode::PROVIDE->value;
        }

        Assert::stringNotEmpty($schemaView['fallback']);

        $fallback = ViewFallbackMode::tryFrom($schemaView['fallback']);

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
