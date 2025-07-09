<?php

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Override;
use Webmozart\Assert\Assert;

readonly class ActionConfigurationRepository implements ActionConfigurationRepositoryInterface
{
    /**
     * @var array<class-string, array<string, ActionConfiguration>>
     */
    private array $indexedConfigurations;

    /**
     * @param ActionConfiguration[] $configurations
     */
    public function __construct(
        private iterable $configurations,
    ) {
        Assert::allIsInstanceOf($configurations, ActionConfiguration::class);

        $indexed = [];

        foreach ($configurations as $configuration) {
            $indexed[$configuration->getEntityClass()][$configuration->getAction()] = $configuration;
        }

        $this->indexedConfigurations = $indexed;
    }

    #[Override]
    public function get(
        string $entityClass,
        string $action,
    ): ActionConfiguration {
        return $this->tryGet($entityClass, $action)
            ??
            throw new CrudEngineMissingConfigurationException(
                $entityClass,
                $action,
            );
    }

    #[Override]
    public function tryGet(
        string $entityClass,
        string $action,
    ): ?ActionConfiguration {
        return $this->indexedConfigurations[$entityClass][$action] ?? null;
    }

    /**
     * @return \Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration[]
     */
    #[Override]
    public function all(): iterable
    {
        return $this->configurations;
    }
}
