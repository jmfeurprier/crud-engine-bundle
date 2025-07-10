<?php

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfigurationLoader;
use Jmf\CrudEngine\Configuration\Schema\SchemaConfigurationLoader;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Override;
use Webmozart\Assert\Assert;

readonly class ActionConfigurationsLoader implements ActionConfigurationsLoaderInterface
{
    public function __construct(
        private SchemaConfigurationLoader $schemaConfigurationLoader,
        private ActionConfigurationLoader $actionConfigurationLoader,
    ) {
    }

    #[Override]
    public function load(array $config): ActionConfigurationsCollection
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

        return new ActionConfigurationsCollection($actionConfigurations);
    }
}
