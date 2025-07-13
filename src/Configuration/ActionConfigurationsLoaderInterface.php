<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;

interface ActionConfigurationsLoaderInterface
{
    /**
     * @param array<string, mixed> $config
     *
     * @throws CrudEngineConfigurationException
     */
    public function load(array $config): ActionConfigurationsCollection;
}
