<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Registry;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Model\CrudAction;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Registry\ActionDefinitionRegistry;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use PHPUnit\Framework\TestCase;

final class ActionDefinitionRegistryTest extends TestCase
{
    /**
     * @var ActionDefinition[]
     */
    private array $actionDefinitions = [];

    private ActionDefinitionRegistry $actionDefinitionRegistry;

    public function testGetReturnsDefinition(): void
    {
        $actionDefinition = $this->givenActionDefinition(Article::class, CrudAction::Create);

        $this->createRepository();

        $result = $this->actionDefinitionRegistry->get(Article::class, CrudAction::Create);

        self::assertSame($actionDefinition, $result);
    }

    public function testGetThrowsWhenMissing(): void
    {
        $this->createRepository();

        $this->expectException(CrudEngineMissingConfigurationException::class);

        $this->actionDefinitionRegistry->get(Article::class, CrudAction::Create);
    }

    public function testTryGetReturnsNullForUnknown(): void
    {
        $this->createRepository();

        $result = $this->actionDefinitionRegistry->tryGet(Article::class, CrudAction::Create);

        self::assertNull($result);
    }

    public function testAllYieldsEveryDefinition(): void
    {
        $createActionDefinition = $this->givenActionDefinition(Article::class, CrudAction::Create);
        $indexActionDefinition  = $this->givenActionDefinition(Article::class, CrudAction::Index);

        $this->createRepository();

        $result = $this->actionDefinitionRegistry->all();

        $all = iterator_to_array($result, false);

        self::assertCount(2, $all);
        self::assertContains($createActionDefinition, $all);
        self::assertContains($indexActionDefinition, $all);
    }

    /**
     * @param class-string $entityClass
     */
    private function givenActionDefinition(
        string $entityClass,
        CrudAction $crudAction,
    ): ActionDefinition {
        $actionDefinition = $this->createStub(ActionDefinition::class);
        $actionDefinition->method('getEntityAction')->willReturn(
            new EntityAction(
                $entityClass,
                $crudAction,
            ),
        );

        $this->actionDefinitions[] = $actionDefinition;

        return $actionDefinition;
    }

    private function createRepository(): void
    {
        $this->actionDefinitionRegistry = new ActionDefinitionRegistry(
            $this->actionDefinitions,
        );
    }
}
