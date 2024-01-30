<?php

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;

interface ActionConfigurationRepositoryFactoryInterface
{
    /**
     * @throws CrudEngineMissingConfigurationException
     */
    public function make(): ActionConfigurationRepositoryInterface;
}
