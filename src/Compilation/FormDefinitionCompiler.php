<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation;

use Jmf\CrudEngine\Definition\FallbackMode;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Symfony\Component\Form\FormTypeInterface;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type CompiledForm array{typeClass: class-string<FormTypeInterface>|null, suggestedClass: non-empty-string, fallback: non-empty-string}
 */
readonly class FormDefinitionCompiler
{
    /**
     * @var list<non-empty-string>
     */
    private const array DEFAULT_FORM_TYPES = [
        "App\\Form\\{{ EntityKey }}\\{{ ActionKey }}Type",
        "App\\Form\\{{ EntityKey }}{{ ActionKey }}Type",
        "App\\Form\\{{ EntityKey }}Type",
    ];

    public function __construct(
        private ConfigurationValueResolver $configurationValueResolver,
        private MapResolver $mapResolver,
        private PatternsResolver $patternsResolver,
    ) {
    }

    /**
     * @param array<string, mixed>                      $schema
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $actionConfig
     *
     * @return CompiledForm
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function compile(
        array $schema,
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): array {
        return [
            'typeClass'      => $this->resolveTypeClass($schema, $keys, $entityClass, $action, $actionConfig),
            'suggestedClass' => $this->resolveSuggestedClass($schema, $keys, $entityClass, $action),
            'fallback'       => $this->resolveFallback($schema),
        ];
    }

    /**
     * @param array<string, mixed>                      $schema
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $actionConfig
     *
     * @return class-string<FormTypeInterface>|null
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function resolveTypeClass(
        array $schema,
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?string {
        if (array_key_exists('form', $actionConfig)) {
            $formConfig = $actionConfig['form'];
            Assert::isMap($formConfig);

            if (array_key_exists('type', $formConfig) && null !== $formConfig['type']) {
                $formTypeClass = $formConfig['type'];

                Assert::string($formTypeClass);
                Assert::classExists($formTypeClass);
                Assert::subclassOf($formTypeClass, FormTypeInterface::class);

                return $formTypeClass;
            }
        }

        foreach ($this->resolveTypePatterns($schema) as $pattern) {
            $class = $this->configurationValueResolver->resolve($pattern, $keys, $entityClass, $action);

            if (class_exists($class) && is_subclass_of($class, FormTypeInterface::class)) {
                return $class;
            }
        }

        return null;
    }

    /**
     * The conventional class a developer should create to customize the form.
     * This is the first discovery pattern expanded for this entity/action
     * (whether it exists yet).
     *
     * @param array<string, mixed>                      $schema
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     *
     * @return non-empty-string
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function resolveSuggestedClass(
        array $schema,
        array $keys,
        string $entityClass,
        string $action,
    ): string {
        $patterns = $this->resolveTypePatterns($schema);

        $pattern = reset($patterns);

        Assert::stringNotEmpty($pattern);

        $suggested = $this->configurationValueResolver->resolve(
            $pattern,
            $keys,
            $entityClass,
            $action,
        );

        Assert::stringNotEmpty($suggested);

        return $suggested;
    }

    /**
     * Form-type discovery patterns, read from `schema.form.type` (falling back
     * to the conventional defaults).
     *
     * @param array<string, mixed> $schema
     *
     * @return list<non-empty-string>
     */
    private function resolveTypePatterns(array $schema): array
    {
        return $this->patternsResolver->resolve(
            $this->mapResolver->resolve($schema, 'form'),
            'type',
            self::DEFAULT_FORM_TYPES,
        );
    }

    /**
     * @param array<string, mixed> $schema
     *
     * @return non-empty-string
     */
    private function resolveFallback(array $schema): string
    {
        $schemaForm = $this->mapResolver->resolve($schema, 'form');

        if (!array_key_exists('fallback', $schemaForm)) {
            return FallbackMode::PROVIDE->value;
        }

        Assert::stringNotEmpty($schemaForm['fallback']);

        $fallback = FallbackMode::tryFrom($schemaForm['fallback']);

        Assert::notNull(
            $fallback,
            sprintf('Unknown form fallback mode "%s".', $schemaForm['fallback']),
        );

        return $fallback->value;
    }
}
