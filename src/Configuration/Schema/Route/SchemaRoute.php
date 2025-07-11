<?php

namespace Jmf\CrudEngine\Configuration\Schema\Route;

use Jmf\CrudEngine\Configuration\Schema\Route\Paths\SchemaRoutePathsCollection;
use Webmozart\Assert\Assert;

readonly class SchemaRoute
{
    public const string DEFAULT_NAME = "{{ entityClass|u.afterLast('\\\\').snake }}.{{ action }}";

    public function __construct(
        private string $name,
        private SchemaRoutePathsCollection $paths,
    ) {
        Assert::stringNotEmpty($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPaths(): SchemaRoutePathsCollection
    {
        return $this->paths;
    }
}
