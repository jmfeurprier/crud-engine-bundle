<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Configuration\Repository;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Repository\ActionConfigurationRepositoryFactory;
use Jmf\CrudEngine\Configuration\Repository\ActionConfigurationHydrator;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use PHPUnit\Framework\TestCase;

final class ActionConfigurationRepositoryFactoryTest extends TestCase
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

        $actionConfigurationRepositoryFactory = new ActionConfigurationRepositoryFactory(
            $resolvedConfigurations,
            $hydrator,
        );

        $result = $actionConfigurationRepositoryFactory->create();

        self::assertSame(
            $actionConfiguration,
            $result->get(Article::class, 'create'),
        );
    }
}
