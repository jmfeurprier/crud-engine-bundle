<?php

namespace Jmf\CrudEngine\Configuration\Schema;

use Jmf\CrudEngine\Configuration\Schema\FormType\FormTypeSchema;
use Jmf\CrudEngine\Configuration\Schema\Helper\HelperSchema;
use Jmf\CrudEngine\Configuration\Schema\Route\RouteSchema;
use Jmf\CrudEngine\Configuration\Schema\View\ViewSchema;

readonly class Schema
{
    public function __construct(
        private FormTypeSchema $formTypes,
        private HelperSchema $helpers,
        private RouteSchema $route,
        private ViewSchema $view,
    ) {
    }

    public function getFormTypes(): FormTypeSchema
    {
        return $this->formTypes;
    }

    public function getHelpers(): HelperSchema
    {
        return $this->helpers;
    }

    public function getRoute(): RouteSchema
    {
        return $this->route;
    }

    public function getView(): ViewSchema
    {
        return $this->view;
    }
}
