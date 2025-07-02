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
     * @param array<class-string, array<string, mixed>> $config
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
            Assert::isMap($entityConfig);
            Assert::keyExists($entityConfig, 'actions');

            $actionsConfig = $entityConfig['actions'];

            Assert::isMap($actionsConfig);

            foreach ($actionsConfig as $action => $actionConfig) {
                Assert::stringNotEmpty($action);
                Assert::isMap($actionConfig);

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
