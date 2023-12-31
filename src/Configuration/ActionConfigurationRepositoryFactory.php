<?php

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;

readonly class ActionConfigurationRepositoryFactory implements ActionConfigurationRepositoryFactoryInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private ActionConfigurationsLoader $actionConfigurationsLoader,
        private array $config,
    ) {
    }

    public function make(): ActionConfigurationRepository
    {
        return new ActionConfigurationRepository(
            $this->getActionConfigurations(),
        );
    }

    /**
     * @return ActionConfiguration[]
     *
     * @throws CrudEngineMissingConfigurationException
     */
    private function getActionConfigurations(): iterable
    {
        return $this->actionConfigurationsLoader->load($this->config);
    }
}
