<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Webmozart\Assert\Assert;

readonly class ActionConfigurationsCollection
{
    /**
     * @var array<class-string, array<string, ActionConfiguration>>
     */
    private array $indexedConfigurations;

    /**
     * @param ActionConfiguration[] $actionConfigurations
     */
    public function __construct(
        private iterable $actionConfigurations,
    ) {
        Assert::allIsInstanceOf($actionConfigurations, ActionConfiguration::class);

        $indexed = [];

        foreach ($actionConfigurations as $actionConfiguration) {
            $indexed[$actionConfiguration->getEntityClass()][$actionConfiguration->getAction()] = $actionConfiguration;
        }

        $this->indexedConfigurations = $indexed;
    }

    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     *
     * @throws CrudEngineMissingConfigurationException
     */
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

    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     */
    public function tryGet(
        string $entityClass,
        string $action,
    ): ?ActionConfiguration {
        return $this->indexedConfigurations[$entityClass][$action] ?? null;
    }

    /**
     * @return ActionConfiguration[]
     */
    public function all(): iterable
    {
        return $this->actionConfigurations;
    }
}
