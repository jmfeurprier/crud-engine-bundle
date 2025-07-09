<?php

namespace Jmf\CrudEngine\Configuration\Schema\View;

use Jmf\CrudEngine\Configuration\Schema\View\Variables\SchemaViewVariablesCollection;

readonly class SchemaViewConfiguration
{
    public function __construct(
        private string $path,
        private SchemaViewVariablesCollection $variables,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getVariables(): SchemaViewVariablesCollection
    {
        return $this->variables;
    }
}
