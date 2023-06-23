<?php

namespace Jmf\CrudEngine\Controller;

interface DeleteActionHelperInterface extends ActionHelperInterface
{
    public function hookBeforeRemove(object $entity): void;

    public function hookAfterRemove(object $entity): void;
}
