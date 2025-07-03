<?php

namespace Jmf\CrudEngine\Tests\Configuration\Action\Route;

use Jmf\CrudEngine\Configuration\Action\Route\RouteConfigurationLoader;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Inflector\EnglishInflector;

class RouteConfigurationLoaderTest extends TestCase
{
    private RouteConfigurationLoader $routeConfigurationLoader;

    #[Override]
    protected function setUp(): void
    {
        $this->routeConfigurationLoader = new RouteConfigurationLoader(
            new EnglishInflector(),
        );
    }

    /**
     * @return array{
     *     0: non-empty-string,
     *     1: non-empty-string,
     *     2: array<string, mixed>,
     *     3: non-empty-string,
     * }[]
     */
    public static function dataProviderClassActionAndRoutePath(): iterable
    {
        return [
            [
                'App\\Entity\\Article',
                'create',
                [],
                'articles/create',
            ],
            [
                'App\\Entity\\Article',
                'read',
                [],
                'articles/{id}',
            ],
            [
                'App\\Entity\\Article',
                'index',
                [],
                'articles',
            ],
            [
                'App\\Entity\\ApiKey',
                'update',
                [],
                'api-keys/{id}/update',
            ],
        ];
    }

    /**
     * @param class-string         $class
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     * @param class-string         $routePath
     *
     * @throws CrudEngineMissingConfigurationException
     */
    #[DataProvider('dataProviderClassActionAndRoutePath')]
    public function testLoad(
        string $class,
        string $action,
        array $actionConfig,
        string $routePath,
    ): void {
        $result = $this->routeConfigurationLoader->load($class, $action, $actionConfig);

        self::assertSame($routePath, $result->getPath());
    }
}
