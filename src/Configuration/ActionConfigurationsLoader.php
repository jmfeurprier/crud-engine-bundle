<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfigurationLoader;
use Jmf\CrudEngine\Configuration\Schema\SchemaLoader;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Override;
use Webmozart\Assert\Assert;

readonly class ActionConfigurationsLoader implements ActionConfigurationsLoaderInterface
{
    public function __construct(
        private SchemaLoader $schemaLoader,
        private ActionConfigurationLoader $actionConfigurationLoader,
    ) {
    }

    #[Override]
    public function load(array $config): ActionConfigurationsCollection
    {
        $schema = $this->schemaLoader->load($config);

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
                    $schema,
                    $entityClass,
                    $action,
                    $actionConfig,
                );
            }
        }

        return new ActionConfigurationsCollection($actionConfigurations);
    }
}
