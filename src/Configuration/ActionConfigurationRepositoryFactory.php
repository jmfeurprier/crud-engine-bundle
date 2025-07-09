<?php

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;
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
     * @throws CrudEngineConfigurationException
     */
    private function getActionConfigurations(): iterable
    {
        return $this->actionConfigurationsLoader->load($this->config);
    }
}
