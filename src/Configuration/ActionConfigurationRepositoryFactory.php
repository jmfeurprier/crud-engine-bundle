<?php

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Override;

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

    #[Override]
    public function make(): ActionConfigurationRepositoryInterface
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
