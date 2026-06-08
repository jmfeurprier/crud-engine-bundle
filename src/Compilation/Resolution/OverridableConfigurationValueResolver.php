<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation\Resolution;

use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Model\CrudAction;
use Webmozart\Assert\Assert;

readonly class OverridableConfigurationValueResolver
{
    public function __construct(
        private SchemaValueExpander $schemaValueExpander,
    ) {
    }

    /**
     * Resolves a value that may be set on the action, then the schema, then a default
     * pattern — expanding placeholders unless it comes from the action override.
     *
     * @param array<string, mixed>                      $config
     * @param non-empty-string                          $configKey
     * @param array<string, mixed>                      $schema
     * @param non-empty-string                          $schemaKey
     * @param non-empty-string                          $default
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     *
     * @return non-empty-string
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function resolve(
        array $config,
        string $configKey,
        array $schema,
        string $schemaKey,
        string $default,
        array $keys,
        string $entityClass,
        CrudAction $action,
    ): string {
        if (array_key_exists($configKey, $config)) {
            Assert::stringNotEmpty($config[$configKey]);

            return $config[$configKey];
        }

        $pattern = $default;

        if (array_key_exists($schemaKey, $schema)) {
            Assert::stringNotEmpty($schema[$schemaKey]);

            $pattern = $schema[$schemaKey];
        }

        $value = $this->schemaValueExpander->expand(
            $pattern,
            $keys,
            [
                'entityClass' => $entityClass,
                'action'      => $action->value,
            ],
        );

        Assert::stringNotEmpty($value);

        return $value;
    }
}
