<?php

namespace Jmf\CrudEngine\Configuration\Schema;

use Jmf\CrudEngine\Configuration\Schema\Route\SchemaRouteConfiguration;
use Jmf\CrudEngine\Configuration\Schema\View\SchemaViewConfiguration;

readonly class SchemaConfiguration
{
    public function __construct(
        private SchemaRouteConfiguration $routeConfiguration,
        private SchemaViewConfiguration $viewConfiguration,
    ) {
    }

    public function getRouteConfiguration(): SchemaRouteConfiguration
    {
        return $this->routeConfiguration;
    }

    public function getViewConfiguration(): SchemaViewConfiguration
    {
        return $this->viewConfiguration;
    }
}
