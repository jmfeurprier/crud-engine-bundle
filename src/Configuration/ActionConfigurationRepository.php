<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;
use Override;
use Webmozart\Assert\Assert;

class ActionConfigurationRepository implements ActionConfigurationRepositoryInterface
{
    private ActionConfigurationsCollection $actionConfigurations;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private readonly ActionConfigurationsLoaderInterface $actionConfigurationsLoader,
        private readonly array $config,
    ) {
        Assert::isMap($config);
    }

    #[Override]
    public function get(
        string $entityClass,
        string $action,
    ): ActionConfiguration {
        return $this->getActionConfigurations()->get($entityClass, $action);
    }

    #[Override]
    public function tryGet(
        string $entityClass,
        string $action,
    ): ?ActionConfiguration {
        return $this->getActionConfigurations()->tryGet($entityClass, $action);
    }

    #[Override]
    public function all(): iterable
    {
        return $this->getActionConfigurations()->all();
    }

    /**
     * @throws CrudEngineConfigurationException
     */
    private function getActionConfigurations(): ActionConfigurationsCollection
    {
        if (!isset($this->actionConfigurations)) {
            $this->actionConfigurations = $this->actionConfigurationsLoader->load($this->config);
        }

        return $this->actionConfigurations;
    }
}
