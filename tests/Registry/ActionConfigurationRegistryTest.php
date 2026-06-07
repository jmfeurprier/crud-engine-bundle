<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Registry;

use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Registry\ActionConfigurationRegistry;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use PHPUnit\Framework\TestCase;

final class ActionConfigurationRegistryTest extends TestCase
{
    private ActionConfigurationRegistry $actionConfigurationRegistry;

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

        $result = $this->actionConfigurationRegistry->get(Article::class, 'create');

        self::assertSame($actionConfiguration, $result);
    }

    public function testGetThrowsWhenMissing(): void
    {
        $this->givenConfiguration([]);

        $this->expectException(CrudEngineMissingConfigurationException::class);

        $this->actionConfigurationRegistry->get(Article::class, 'create');
    }

    public function testTryGetReturnsNullForUnknown(): void
    {
        $this->givenConfiguration([]);

        $result = $this->actionConfigurationRegistry->tryGet(Article::class, 'unknown');

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

        $result = $this->actionConfigurationRegistry->all();

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
        $this->actionConfigurationRegistry = new ActionConfigurationRegistry(
            $configuration,
        );
    }
}
