<?php

namespace Jmf\CrudEngine\Configuration\Schema\Route;

use Jmf\CrudEngine\Configuration\Schema\Route\Paths\SchemaRoutePathsCollection;
use Webmozart\Assert\Assert;

readonly class SchemaRouteConfiguration
{
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
