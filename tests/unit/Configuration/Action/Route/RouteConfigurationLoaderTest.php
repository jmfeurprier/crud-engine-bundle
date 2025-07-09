<?php

namespace Jmf\CrudEngine\Tests\Configuration\Action\Route;

use Jmf\CrudEngine\Configuration\Entities\Action\Route\RouteConfigurationLoader;
use Jmf\CrudEngine\Configuration\Schema\Route\Paths\SchemaRoutePathsCollection;
use Jmf\CrudEngine\Configuration\Schema\Route\SchemaRouteConfiguration;
use Jmf\CrudEngine\Configuration\Schema\SchemaConfiguration;
use Jmf\CrudEngine\Configuration\Schema\View\SchemaViewConfiguration;
use Jmf\CrudEngine\Configuration\Schema\View\Variables\SchemaViewVariablesCollection;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\TemplateRendering\TemplateRenderer;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

class RouteConfigurationLoaderTest extends TestCase
{
    private RouteConfigurationLoader $routeConfigurationLoader;

    #[Override]
    protected function setUp(): void
    {
        $twigEnvironment = new Environment(new ArrayLoader());
        $twigEnvironment->addExtension(new StringExtension());

        $this->routeConfigurationLoader = new RouteConfigurationLoader(
            new TemplateRenderer($twigEnvironment),
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
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     * @param class-string         $routePath
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    #[DataProvider('dataProviderClassActionAndRoutePath')]
    public function testLoad(
        string $entityClass,
        string $action,
        array $actionConfig,
        string $routePath,
    ): void {
        $schemaConfiguration = new SchemaConfiguration(
            new SchemaRouteConfiguration('foo', SchemaRoutePathsCollection::createEmpty()),
            new SchemaViewConfiguration('bar', SchemaViewVariablesCollection::createEmpty()),
        );

        $result = $this->routeConfigurationLoader->load(
            $schemaConfiguration,
            $entityClass,
            $action,
            $actionConfig,
        );

        self::assertSame($routePath, $result->getPath());
    }
}
