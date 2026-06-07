<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Registry;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Registry\ActionConfigurationRegistryFactory;
use Jmf\CrudEngine\Registry\ActionConfigurationHydrator;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use PHPUnit\Framework\TestCase;

final class ActionConfigurationRegistryFactoryTest extends TestCase
{
    public function testCreateBuildsRepositoryFromHydratedConfigurations(): void
    {
        $resolvedConfigurations = [];

        $actionConfiguration = $this->createStub(ActionConfiguration::class);

        $hydrator = $this->createMock(ActionConfigurationHydrator::class);
        $hydrator
            ->expects(self::once())
            ->method('hydrate')
            ->with($resolvedConfigurations)
            ->willReturn(
                [
                    Article::class => [
                        'create' => $actionConfiguration,
                    ],
                ],
            )
        ;

        $actionConfigurationRegistryFactory = new ActionConfigurationRegistryFactory(
            $resolvedConfigurations,
            $hydrator,
        );

        $result = $actionConfigurationRegistryFactory->create();

        self::assertSame(
            $actionConfiguration,
            $result->get(Article::class, 'create'),
        );
    }
}
