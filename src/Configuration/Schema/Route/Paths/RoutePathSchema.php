<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Schema\Route\Paths;

use Webmozart\Assert\Assert;

readonly class RoutePathSchema
{
    /**
     * @const array<non-empty-string, non-empty-string>
     */
    private const array DEFAULT_PATHS = [
        'create' => "{{ entitydashkeys }}/create",
        'delete' => "{{ entitydashkeys }}/{id}/delete",
        'index'  => "{{ entitydashkeys }}",
        'read'   => "{{ entitydashkeys }}/{id}",
        'update' => "{{ entitydashkeys }}/{id}/update",
    ];

    /**
     * @var array<non-empty-string, string> $paths
     */
    private array $paths;

    public static function createDefault(): self
    {
        return new self(self::DEFAULT_PATHS);
    }

    /**
     * @param array<non-empty-string, string> $paths
     */
    public function __construct(
        array $paths,
    ) {
        Assert::isMap($paths);
        Assert::allString($paths);

        $this->paths = array_merge(
            self::DEFAULT_PATHS,
            $paths,
        );
    }

    /**
     * @param non-empty-string $action
     */
    public function tryGet(string $action): ?string
    {
        return $this->paths[$action] ?? null;
    }
}
