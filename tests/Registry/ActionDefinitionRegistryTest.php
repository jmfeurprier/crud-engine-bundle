<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Registry;

use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Model\ActionDefinition;
use Jmf\CrudEngine\Registry\ActionDefinitionRegistry;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use PHPUnit\Framework\TestCase;

final class ActionDefinitionRegistryTest extends TestCase
{
    private ActionDefinitionRegistry $actionDefinitionRegistry;

    public function testGetReturnsConfiguration(): void
    {
        $actionDefinition = $this->createStub(ActionDefinition::class);

        $this->givenConfiguration(
            [
                Article::class => [
                    'create' => $actionDefinition,
                ],
            ],
        );

        $result = $this->actionDefinitionRegistry->get(Article::class, 'create');

        self::assertSame($actionDefinition, $result);
    }

    public function testGetThrowsWhenMissing(): void
    {
        $this->givenConfiguration([]);

        $this->expectException(CrudEngineMissingConfigurationException::class);

        $this->actionDefinitionRegistry->get(Article::class, 'create');
    }

    public function testTryGetReturnsNullForUnknown(): void
    {
        $this->givenConfiguration([]);

        $result = $this->actionDefinitionRegistry->tryGet(Article::class, 'unknown');

        self::assertNull($result);
    }

    public function testAllYieldsEveryConfiguration(): void
    {
        $createActionDefinition = $this->createStub(ActionDefinition::class);
        $indexActionDefinition  = $this->createStub(ActionDefinition::class);

        $this->givenConfiguration(
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
     * @param array<class-string, array<non-empty-string, ActionDefinition>> $configuration
     */
    private function givenConfiguration(array $configuration): void
    {
        $this->actionDefinitionRegistry = new ActionDefinitionRegistry(
            $configuration,
        );
    }
}
