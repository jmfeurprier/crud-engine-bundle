<?php

namespace Jmf\CrudEngine\Configuration\Entities\Action\View;

use Jmf\CrudEngine\Configuration\Entities\Action\View\Variables\ActionViewVariablesCollection;

readonly class ActionViewConfiguration
{
    public function __construct(
        private string $path,
        private ActionViewVariablesCollection $variables,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getVariables(): ActionViewVariablesCollection
    {
        return $this->variables;
    }
}
