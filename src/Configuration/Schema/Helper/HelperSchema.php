<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Schema\Helper;

use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Webmozart\Assert\Assert;

readonly class HelperSchema
{
    /**
     * @const non-empty-string[]
     */
    private const iterable DEFAULT_CLASSES = [
        "App\\Controller\\{{ EntityKey }}\\{{ ActionKey }}ActionHelper",
        "App\\Controller\\{{ EntityKey }}{{ ActionKey }}ActionHelper",
    ];

    public static function createDefault(): self
    {
        return new self(self::DEFAULT_CLASSES);
    }

    /**
     * @param non-empty-string[] $classes
     */
    public function __construct(
        private iterable $classes,
    ) {
        Assert::allStringNotEmpty($classes);
    }

    /**
     * @return non-empty-string[]
     */
    public function all(): iterable
    {
        return $this->classes;
    }

    /**
     * @param array<non-empty-string, non-empty-string> $keys
     * @param array<non-empty-string, mixed>            $arguments
     *
     * @return non-empty-string[]
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function expand(
        SchemaValueExpander $schemaValueExpander,
        array $keys,
        array $arguments,
    ): iterable {
        $expanded = array_map(
            static fn(
                string $value,
            ): string => $schemaValueExpander->expand($value, $keys, $arguments),
            (array) $this->classes,
        );

        Assert::allStringNotEmpty($expanded);

        return $expanded;
    }
}
