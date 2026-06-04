<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Repository;

use Jmf\CrudEngine\Configuration\ActionConfigurationResolver;

/**
 * @phpstan-import-type ResolvedConfigurations from ActionConfigurationResolver
 */
readonly class ActionConfigurationRepositoryFactory
{
    /**
     * @param ResolvedConfigurations $resolvedConfigurations
     */
    public function __construct(
        private array $resolvedConfigurations,
        private ActionConfigurationHydrator $hydrator,
    ) {
    }

    public function create(): ActionConfigurationRepositoryInterface
    {
        return new ActionConfigurationRepository(
            $this->hydrator->hydrate($this->resolvedConfigurations),
        );
    }
}
