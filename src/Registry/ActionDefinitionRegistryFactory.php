<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Registry;

use Jmf\CrudEngine\Compilation\Compiler;

/**
 * @phpstan-import-type CompiledDefinitions from Compiler
 */
readonly class ActionDefinitionRegistryFactory
{
    /**
     * @param CompiledDefinitions $compiledDefinitions
     */
    public function __construct(
        private array $compiledDefinitions,
        private ActionDefinitionHydrator $actionDefinitionHydrator,
    ) {
    }

    public function create(): ActionDefinitionRegistryInterface
    {
        return new ActionDefinitionRegistry(
            $this->actionDefinitionHydrator->hydrate($this->compiledDefinitions),
        );
    }
}
