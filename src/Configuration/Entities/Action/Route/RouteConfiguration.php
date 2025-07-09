<?php

namespace Jmf\CrudEngine\Configuration\Entities\Action\Route;

use Jmf\CrudEngine\Configuration\KeyStringCollection;

readonly class RouteConfiguration
{
    public function __construct(
        private string $name,
        private string $path,
        private KeyStringCollection $requirements,
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

    public function getRequirements(): KeyStringCollection
    {
        return $this->requirements;
    }
}
