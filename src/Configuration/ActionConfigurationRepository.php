<?php

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Override;
use Webmozart\Assert\Assert;

readonly class ActionConfigurationRepository implements ActionConfigurationRepositoryInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private ActionConfigurationsLoaderInterface $actionConfigurationsLoader,
        private array $config,
    ) {
        Assert::isMap($config);
    }

    #[Override]
    public function get(
        string $entityClass,
        string $action,
    ): ActionConfiguration {
        return $this->actionConfigurationsLoader->load($this->config)->get($entityClass, $action);
    }

    #[Override]
    public function tryGet(
        string $entityClass,
        string $action,
    ): ?ActionConfiguration {
        return $this->actionConfigurationsLoader->load($this->config)->tryGet($entityClass, $action);
    }

    #[Override]
    public function all(): iterable
    {
        return $this->actionConfigurationsLoader->load($this->config)->all();
    }
}
