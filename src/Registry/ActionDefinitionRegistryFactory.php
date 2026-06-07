<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Registry;

use Jmf\CrudEngine\Compilation\ActionDefinitionCompiler;

/**
 * @phpstan-import-type CompiledDefinitions from ActionDefinitionCompiler
 */
readonly class ActionDefinitionRegistryFactory
{
    /**
     * @param CompiledDefinitions $resolvedConfigurations
     */
    public function __construct(
        private array $resolvedConfigurations,
        private ActionDefinitionHydrator $actionDefinitionHydrator,
    ) {
    }

    public function create(): ActionDefinitionRegistryInterface
    {
        return new ActionDefinitionRegistry(
            $this->actionDefinitionHydrator->hydrate($this->resolvedConfigurations),
        );
    }
}
