<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Schema\Keys;

use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Webmozart\Assert\Assert;

readonly class KeySchema
{
    /**
     * @const array<non-empty-string, non-empty-string>
     */
    private const array DEFAULT_KEYS = [
        'ActionKey'      => "{{ action|u.camel.title }}",
        'ActionKeys'     => "{{ action|u.camel.title|plural }}",
        'actionKey'      => "{{ action|u.camel }}",
        'actionKeys'     => "{{ action|u.camel|plural }}",
        'action_key'     => "{{ action|u.snake }}",
        'action_keys'    => "{{ action|u.snake|plural }}",
        'actiondashkey'  => "{{ action|u.kebab }}",
        'actiondashkeys' => "{{ action|u.kebab|plural }}",
        'EntityKey'      => "{{ entityClass|u.afterLast('\\\\').camel.title }}",
        'EntityKeys'     => "{{ entityClass|u.afterLast('\\\\').camel.title|plural }}",
        'entityKey'      => "{{ entityClass|u.afterLast('\\\\').camel }}",
        'entityKeys'     => "{{ entityClass|u.afterLast('\\\\').camel|plural }}",
        'entity_key'     => "{{ entityClass|u.afterLast('\\\\').snake }}",
        'entity_keys'    => "{{ entityClass|u.afterLast('\\\\').snake|plural }}",
        'entitydashkey'  => "{{ entityClass|u.afterLast('\\\\').kebab }}",
        'entitydashkeys' => "{{ entityClass|u.afterLast('\\\\').kebab|plural }}",
    ];

    /**
     * @var array<non-empty-string, non-empty-string>
     */
    private array $keys;

    public static function createDefault(): self
    {
        return new self(
            self::DEFAULT_KEYS,
        );
    }

    /**
     * @param array<non-empty-string, non-empty-string> $keys
     */
    public function __construct(
        array $keys,
    ) {
        Assert::isMap($keys);
        Assert::allStringNotEmpty($keys);

        $this->keys = array_merge(
            self::DEFAULT_KEYS,
            $keys,
        );
    }

    /**
     * @param array<string, mixed> $arguments
     *
     * @return array<non-empty-string, non-empty-string>
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function expand(
        SchemaValueExpander $schemaValueExpander,
        array $arguments,
    ): array {
        $keys = array_map(
            static fn(
                $value,
            ): string => $schemaValueExpander->expand($value, [], $arguments),
            $this->keys,
        );

        Assert::allStringNotEmpty($keys);

        return $keys;
    }
}
