<?php

namespace Jmf\CrudEngine\Configuration\Entities\Action\View;

use Jmf\CrudEngine\Configuration\KeyStringCollection;

readonly class ActionViewConfiguration
{
    public function __construct(
        private string $path,
        private KeyStringCollection $variables,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getVariables(): KeyStringCollection
    {
        return $this->variables;
    }
}
