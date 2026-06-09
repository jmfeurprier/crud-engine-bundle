<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Registry;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Model\CrudAction;
use Override;
use Webmozart\Assert\Assert;

readonly class ActionDefinitionRegistry implements ActionDefinitionRegistryInterface
{
    /**
     * @var array<class-string, non-empty-array<string, ActionDefinition>>
     */
    private array $indexed;

    /**
     * @param ActionDefinition[] $actionDefinitions
     */
    public function __construct(
        private iterable $actionDefinitions,
    ) {
        Assert::allIsInstanceOf($actionDefinitions, ActionDefinition::class);

        $indexed = [];

        foreach ($this->actionDefinitions as $actionDefinition) {
            $entityAction = $actionDefinition->getEntityAction();
            $entityClass  = $entityAction->getEntityClass();
            $action       = $entityAction->getAction()->value;

            $indexed[$entityClass][$action] = $actionDefinition;
        }

        $this->indexed = $indexed;
    }

    #[Override]
    public function get(
        string $entityClass,
        CrudAction $action,
    ): ActionDefinition {
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
        CrudAction $action,
    ): ?ActionDefinition {
        return $this->indexed[$entityClass][$action->value] ?? null;
    }

    #[Override]
    public function all(): iterable
    {
        yield from $this->actionDefinitions;
    }
}
