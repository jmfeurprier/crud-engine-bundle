<?php

namespace Jmf\CrudEngine\Configuration\Schema\Route;

use Webmozart\Assert\Assert;

readonly class SchemaRouteConfiguration
{
    public function __construct(
        private string $name,
    ) {
        Assert::stringNotEmpty($name);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
