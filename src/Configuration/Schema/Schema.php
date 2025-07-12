<?php

namespace Jmf\CrudEngine\Configuration\Schema;

use Jmf\CrudEngine\Configuration\Schema\FormType\SchemaFormTypesCollection;
use Jmf\CrudEngine\Configuration\Schema\Helper\SchemaHelpersCollection;
use Jmf\CrudEngine\Configuration\Schema\Route\SchemaRoute;
use Jmf\CrudEngine\Configuration\Schema\View\SchemaView;

readonly class Schema
{
    public function __construct(
        private SchemaFormTypesCollection $formTypes,
        private SchemaHelpersCollection $helpers,
        private SchemaRoute $route,
        private SchemaView $view,
    ) {
    }

    public function getFormTypes(): SchemaFormTypesCollection
    {
        return $this->formTypes;
    }

    public function getHelpers(): SchemaHelpersCollection
    {
        return $this->helpers;
    }

    public function getRoute(): SchemaRoute
    {
        return $this->route;
    }

    public function getView(): SchemaView
    {
        return $this->view;
    }
}
