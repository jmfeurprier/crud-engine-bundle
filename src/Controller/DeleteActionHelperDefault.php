<?php

namespace Jmf\CrudEngine\Controller;

class DeleteActionHelperDefault implements DeleteActionHelperInterface
{
    public function hookBeforeRemove(object $entity): void
    {
    }

    public function hookAfterRemove(object $entity): void
    {
    }
}
