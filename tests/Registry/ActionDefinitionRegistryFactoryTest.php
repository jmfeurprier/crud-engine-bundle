<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Registry;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Registry\ActionDefinitionHydrator;
use Jmf\CrudEngine\Registry\ActionDefinitionRegistryFactory;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use PHPUnit\Framework\TestCase;

final class ActionDefinitionRegistryFactoryTest extends TestCase
{
    public function testCreateBuildsRepositoryFromHydratedConfigurations(): void
    {
        $resolvedConfigurations = [];

        $actionDefinition = $this->createStub(ActionDefinition::class);

        $hydrator = $this->createMock(ActionDefinitionHydrator::class);
        $hydrator
            ->expects(self::once())
            ->method('hydrate')
            ->with($resolvedConfigurations)
            ->willReturn(
                [
                    Article::class => [
                        'create' => $actionDefinition,
                    ],
                ],
            )
        ;

        $actionDefinitionRegistryFactory = new ActionDefinitionRegistryFactory(
            $resolvedConfigurations,
            $hydrator,
        );

        $result = $actionDefinitionRegistryFactory->create();

        self::assertSame(
            $actionDefinition,
            $result->get(Article::class, 'create'),
        );
    }
}
