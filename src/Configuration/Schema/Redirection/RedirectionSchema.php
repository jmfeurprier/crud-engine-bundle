<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Schema\Redirection;

use Webmozart\Assert\Assert;

readonly class RedirectionSchema
{
    /**
     * @const array<non-empty-string, non-empty-string>
     */
    private const array DEFAULT_ROUTES = [
        'create' => [
            'route'      => "{{ entityClass|u.afterLast('\\\\').snake }}.read",
            'parameters' => [
                'id' => '{{ _entity.id }}',
            ],
        ],
        'delete' => [
            'route'      => "{{ entityClass|u.afterLast('\\\\').snake }}.index",
            'parameters' => [
            ],
        ],
        'update' => [
            'route'      => "{{ entityClass|u.afterLast('\\\\').snake }}.read",
            'parameters' => [
                'id' => '{{ _entity.id }}',
            ],
        ],
    ];

    /**
     * @var array<non-empty-string, RedirectionRouteSchema>
     */
    private array $routes;

    public static function createDefault(): self
    {
        return new self([]);
    }

    /**
     * @param array<non-empty-string, RedirectionRouteSchema> $routeSchemas
     */
    public function __construct(
        array $routeSchemas,
    ) {
        Assert::isMap($routeSchemas);
        Assert::allIsInstanceOf($routeSchemas, RedirectionRouteSchema::class);

        // @todo Static initialization (optimization).
        $defaults = array_map(
            static fn(
                array $routeDefault,
            ): RedirectionRouteSchema => new RedirectionRouteSchema(
                $routeDefault['route'],
                $routeDefault['parameters'],
            ),
            self::DEFAULT_ROUTES,
        );

        $this->routes = array_merge(
            $defaults,
            $routeSchemas,
        );
    }

    /**
     * @return array<non-empty-string, RedirectionRouteSchema>
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * @param non-empty-string $action
     */
    public function tryGet(string $action): ?RedirectionRouteSchema
    {
        return $this->routes[$action] ?? null;
    }
}
