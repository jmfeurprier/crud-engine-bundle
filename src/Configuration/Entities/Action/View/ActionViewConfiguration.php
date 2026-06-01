<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\View;

use Jmf\CrudEngine\Configuration\Entities\Action\View\Variables\ActionViewVariablesCollection;
use Jmf\CrudEngine\Configuration\Schema\View\ViewFallbackMode;

readonly class ActionViewConfiguration
{
    public function __construct(
        private string $path,
        private ActionViewVariablesCollection $variables,
        private ViewFallbackMode $viewFallbackMode,
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

    public function getViewFallbackMode(): ViewFallbackMode
    {
        return $this->viewFallbackMode;
    }
}
