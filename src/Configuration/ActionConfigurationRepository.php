<?php

namespace Jmf\CrudEngine\Configuration;

use DomainException;
use Webmozart\Assert\Assert;

class ActionConfigurationRepository
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

    /**
     * @param class-string $entityClass
     */
    public function get(
        string $entityClass,
        string $action,
    ): ActionConfiguration {
        return $this->indexedConfigurations[$entityClass][$action] ?? throw new DomainException(); // @todo Message.
    }

    /**
     * @return ActionConfiguration[]
     */
    public function all(): iterable
    {
        return $this->configurations;
    }
}
