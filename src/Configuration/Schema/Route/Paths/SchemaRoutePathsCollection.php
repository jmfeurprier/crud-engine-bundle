<?php

namespace Jmf\CrudEngine\Configuration\Schema\Route\Paths;

use Webmozart\Assert\Assert;

readonly class SchemaRoutePathsCollection
{
    private const array DEFAULT_PATHS = [
        'create' => "{{ entityClass|u.afterLast('\\\\').kebab|plural }}/create",
        'delete' => "{{ entityClass|u.afterLast('\\\\').kebab|plural }}/{id}/delete",
        'index'  => "{{ entityClass|u.afterLast('\\\\').kebab|plural }}",
        'read'   => "{{ entityClass|u.afterLast('\\\\').kebab|plural }}/{id}",
        'update' => "{{ entityClass|u.afterLast('\\\\').kebab|plural }}/{id}/update",
    ];

    public static function createEmpty(): self
    {
        return new self([]);
    }

    /**
     * @param array<non-empty-string, non-empty-string> $paths
     */
    public function __construct(
        private array $paths,
    ) {
        Assert::isMap($paths);
        Assert::allStringNotEmpty($paths);
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function all(): array
    {
        return $this->paths;
    }

    /**
     * @param non-empty-string $action
     *
     * @return null|non-empty-string
     */
    public function tryGet(string $action): ?string
    {
        return $this->paths[$action]
            ??
            self::DEFAULT_PATHS[$action]
            ??
            null;
    }
}
