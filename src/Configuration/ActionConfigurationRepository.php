<?php

namespace Jmf\CrudEngine\Configuration;

use DomainException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Override;
use Webmozart\Assert\Assert;

class ActionConfigurationRepository implements ActionConfigurationRepositoryInterface
{
    /**
     * @var array<class-string, array<string, ActionConfiguration>>
     */
    private array $indexedConfigurations = [];

    /**
     * @param ActionConfiguration[] $configurations
     */
    public function __construct(
        private readonly iterable $configurations,
    ) {
        Assert::allIsInstanceOf($configurations, ActionConfiguration::class);

        foreach ($configurations as $configuration) {
            $this->addConfiguration($configuration);
        }
    }

    private function addConfiguration(ActionConfiguration $configuration): void
    {
        $this->indexedConfigurations[$configuration->getEntityClass()][$configuration->getAction()] = $configuration;
    }

    #[Override]
    public function get(
        string $entityClass,
        string $action,
    ): ActionConfiguration {
        return $this->tryGet($entityClass, $action) ?? throw new CrudEngineMissingConfigurationException();
    }

    #[Override]
    public function tryGet(
        string $entityClass,
        string $action,
    ): ?ActionConfiguration {
        return $this->indexedConfigurations[$entityClass][$action] ?? null;
    }

    /**
     * @return ActionConfiguration[]
     */
    #[Override]
    public function all(): iterable
    {
        return $this->configurations;
    }
}
