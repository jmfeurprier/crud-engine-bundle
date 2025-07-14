<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Schema\Route;

use Jmf\CrudEngine\Configuration\Schema\Route\Paths\RoutePathSchema;
use Webmozart\Assert\Assert;

readonly class RouteSchema
{
    /**
     * @const non-empty-string
     */
    public const string DEFAULT_NAME = "{{ entity_key }}.{{ action_key }}";

    public function __construct(
        private string $name,
        private RoutePathSchema $paths,
    ) {
        Assert::stringNotEmpty($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPaths(): RoutePathSchema
    {
        return $this->paths;
    }
}
