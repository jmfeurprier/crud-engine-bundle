<?php

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;

interface ActionConfigurationRepositoryFactoryInterface
{
    /**
     * @throws CrudEngineConfigurationException
     */
    public function make(): ActionConfigurationRepositoryInterface;
}
