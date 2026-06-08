<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Registry;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Model\CrudAction;
use Jmf\CrudEngine\Registry\ActionDefinitionRegistry;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use PHPUnit\Framework\TestCase;

final class ActionDefinitionRegistryTest extends TestCase
{
    private ActionDefinitionRegistry $actionDefinitionRegistry;

    public function testGetReturnsDefinition(): void
    {
        $actionDefinition = $this->createStub(ActionDefinition::class);

        $this->givenDefinitions(
            [
                Article::class => [
                    'create' => $actionDefinition,
                ],
            ],
        );

        $result = $this->actionDefinitionRegistry->get(Article::class, CrudAction::Create);

        self::assertSame($actionDefinition, $result);
    }

    public function testGetThrowsWhenMissing(): void
    {
        $this->givenDefinitions([]);

        $this->expectException(CrudEngineMissingConfigurationException::class);

        $this->actionDefinitionRegistry->get(Article::class, CrudAction::Create);
    }

    public function testTryGetReturnsNullForUnknown(): void
    {
        $this->givenDefinitions([]);

        $result = $this->actionDefinitionRegistry->tryGet(Article::class, CrudAction::Create);

        self::assertNull($result);
    }

    public function testAllYieldsEveryDefinition(): void
    {
        $createActionDefinition = $this->createStub(ActionDefinition::class);
        $indexActionDefinition  = $this->createStub(ActionDefinition::class);

        $this->givenDefinitions(
            [
                Article::class => [
                    'create' => $createActionDefinition,
                    'index'  => $indexActionDefinition,
                ],
            ],
        );

        $result = $this->actionDefinitionRegistry->all();

        $all = iterator_to_array($result, false);

        self::assertCount(2, $all);
        self::assertContains($createActionDefinition, $all);
        self::assertContains($indexActionDefinition, $all);
    }

    /**
     * @param array<class-string, array<non-empty-string, ActionDefinition>> $definitions
     */
    private function givenDefinitions(array $definitions): void
    {
        $this->actionDefinitionRegistry = new ActionDefinitionRegistry(
            $definitions,
        );
    }
}
