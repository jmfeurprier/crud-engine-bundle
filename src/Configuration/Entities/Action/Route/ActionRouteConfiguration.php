<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\Route;

use Jmf\CrudEngine\Configuration\Entities\Action\Route\Requirements\ActionRouteRequirementCollection;

readonly class ActionRouteConfiguration
{
    public function __construct(
        private string $name,
        private string $path,
        private ActionRouteRequirementCollection $requirements,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getRequirements(): ActionRouteRequirementCollection
    {
        return $this->requirements;
    }
}
