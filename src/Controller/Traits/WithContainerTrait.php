<?php

namespace Jmf\CrudEngine\Controller\Traits;

use Symfony\Component\DependencyInjection\ContainerInterface;

trait WithContainerTrait
{
    private ContainerInterface $container;

    private function getService(string $id): object
    {
        return $this->container->get($id);
    }
}
