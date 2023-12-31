<?php

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Webmozart\Assert\Assert;

readonly class ActionConfigurationsLoader
{
    public function __construct(
        private ActionConfigurationLoader $actionConfigurationLoader,
    ) {
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return ActionConfiguration[]
     *
     * @throws CrudEngineMissingConfigurationException
     */
    public function load(array $config): iterable
    {
        Assert::isMap($config);

        $actionConfigurations = [];

        foreach ($config as $entityClass => $entityConfig) {
            Assert::classExists($entityClass);
            Assert::isArray($entityConfig);
            Assert::keyExists($entityConfig, 'actions');
            Assert::isMap($entityConfig['actions']);

            foreach ($entityConfig['actions'] as $action => $actionConfig) {
                Assert::isArray($actionConfig);

                $actionConfigurations[] = $this->actionConfigurationLoader->load(
                    $entityClass,
                    $action,
                    $entityConfig,
                    $actionConfig,
                );
            }
        }

        return $actionConfigurations;
    }
}
