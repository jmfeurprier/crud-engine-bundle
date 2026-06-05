<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Configuration\Repository;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Repository\ActionConfigurationRepository;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use PHPUnit\Framework\TestCase;

final class ActionConfigurationRepositoryTest extends TestCase
{
    private ActionConfigurationRepository $actionConfigurationRepository;

    public function testGetReturnsConfiguration(): void
    {
        $actionConfiguration = $this->createStub(ActionConfiguration::class);

        $this->givenConfiguration(
            [
                Article::class => [
                    'create' => $actionConfiguration,
                ],
            ],
        );

        $result = $this->actionConfigurationRepository->get(Article::class, 'create');

        self::assertSame($actionConfiguration, $result);
    }

    public function testGetThrowsWhenMissing(): void
    {
        $this->givenConfiguration([]);

        $this->expectException(CrudEngineMissingConfigurationException::class);

        $this->actionConfigurationRepository->get(Article::class, 'create');
    }

    public function testTryGetReturnsNullForUnknown(): void
    {
        $this->givenConfiguration([]);

        $result = $this->actionConfigurationRepository->tryGet(Article::class, 'unknown');

        self::assertNull($result);
    }

    public function testAllYieldsEveryConfiguration(): void
    {
        $createActionConfiguration = $this->createStub(ActionConfiguration::class);
        $indexActionConfiguration  = $this->createStub(ActionConfiguration::class);

        $this->givenConfiguration(
            [
                Article::class => [
                    'create' => $createActionConfiguration,
                    'index'  => $indexActionConfiguration,
                ],
            ],
        );

        $result = $this->actionConfigurationRepository->all();

        $all = iterator_to_array($result, false);

        self::assertCount(2, $all);
        self::assertContains($createActionConfiguration, $all);
        self::assertContains($indexActionConfiguration, $all);
    }

    /**
     * @param array<class-string, array<non-empty-string, ActionConfiguration>> $configuration
     */
    private function givenConfiguration(array $configuration): void
    {
        $this->actionConfigurationRepository = new ActionConfigurationRepository(
            $configuration,
        );
    }
}
