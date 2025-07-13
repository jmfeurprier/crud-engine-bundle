<?php

namespace Jmf\CrudEngine\Configuration\Schema;

use Jmf\CrudEngine\Configuration\Schema\FormType\FormTypeSchema;
use Jmf\CrudEngine\Configuration\Schema\Helper\HelperSchema;
use Jmf\CrudEngine\Configuration\Schema\Keys\KeySchema;
use Jmf\CrudEngine\Configuration\Schema\Route\RouteSchema;
use Jmf\CrudEngine\Configuration\Schema\View\ViewSchema;

readonly class Schema
{
    public function __construct(
        private KeySchema $keySchema,
        private FormTypeSchema $formTypeSchema,
        private HelperSchema $helperSchema,
        private RouteSchema $routeSchema,
        private ViewSchema $viewSchema,
    ) {
    }

    public function getKeySchema(): KeySchema
    {
        return $this->keySchema;
    }

    public function getFormTypeSchema(): FormTypeSchema
    {
        return $this->formTypeSchema;
    }

    public function getHelperSchema(): HelperSchema
    {
        return $this->helperSchema;
    }

    public function getRouteSchema(): RouteSchema
    {
        return $this->routeSchema;
    }

    public function getViewSchema(): ViewSchema
    {
        return $this->viewSchema;
    }
}
