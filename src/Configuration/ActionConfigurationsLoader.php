<?php

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfigurationLoader;
use Jmf\CrudEngine\Configuration\Schema\SchemaConfigurationLoader;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Webmozart\Assert\Assert;

readonly class ActionConfigurationsLoader
{
    public function __construct(
        private SchemaConfigurationLoader $schemaConfigurationLoader,
        private ActionConfigurationLoader $actionConfigurationLoader,
    ) {
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return ActionConfiguration[]
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    public function load(array $config): iterable
    {
        $schemaConfiguration = $this->schemaConfigurationLoader->load($config);

        Assert::keyExists($config, 'entities');
        $entitiesConfig = $config['entities'];
        Assert::isMap($entitiesConfig);

        $actionConfigurations = [];

        foreach ($entitiesConfig as $entityClass => $entityConfig) {
            Assert::classExists($entityClass);
            Assert::isMap($entityConfig);

            if (!array_key_exists('actions', $entityConfig)) {
                throw new CrudEngineMissingConfigurationException(
                    entityClass:      $entityClass,
                    configurationKey: 'actions',
                );
            }

            $actionsConfig = $entityConfig['actions'];
            Assert::isMap($actionsConfig);

            foreach ($actionsConfig as $action => $actionConfig) {
                Assert::stringNotEmpty($action);
                Assert::isMap($actionConfig);

                $actionConfigurations[] = $this->actionConfigurationLoader->load(
                    $schemaConfiguration,
                    $entityClass,
                    $action,
                    $actionConfig,
                );
            }
        }

        return $actionConfigurations;
    }
}
