<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Registry;

use Jmf\CrudEngine\Resolution\ActionConfigurationResolver;

/**
 * @phpstan-import-type ResolvedConfigurations from ActionConfigurationResolver
 */
readonly class ActionConfigurationRegistryFactory
{
    /**
     * @param ResolvedConfigurations $resolvedConfigurations
     */
    public function __construct(
        private array $resolvedConfigurations,
        private ActionConfigurationHydrator $actionConfigurationHydrator,
    ) {
    }

    public function create(): ActionConfigurationRegistryInterface
    {
        return new ActionConfigurationRegistry(
            $this->actionConfigurationHydrator->hydrate($this->resolvedConfigurations),
        );
    }
}
