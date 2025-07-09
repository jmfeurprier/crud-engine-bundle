<?php

namespace Jmf\CrudEngine\Tests\Configuration\Action\View;

use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfigurationLoader;
use Jmf\CrudEngine\Configuration\Schema\Route\Paths\SchemaRoutePathsCollection;
use Jmf\CrudEngine\Configuration\Schema\Route\SchemaRouteConfiguration;
use Jmf\CrudEngine\Configuration\Schema\SchemaConfiguration;
use Jmf\CrudEngine\Configuration\Schema\View\SchemaViewConfiguration;
use Jmf\CrudEngine\Configuration\Schema\View\Variables\SchemaViewVariablesCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ViewConfigurationLoaderTest extends TestCase
{
    private ActionViewConfigurationLoader $viewConfigurationLoader;

    protected function setUp(): void
    {
        $this->viewConfigurationLoader =
            new \Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfigurationLoader();
    }

    /**
     * @return array{
     *     0: non-empty-string,
     *     1: non-empty-string,
     *     2: array<string, mixed>,
     *     3: non-empty-string,
     *     4: array<string, string>,
     * }[]
     */
    public static function dataProviderClassActionAndViewPath(): iterable
    {
        return [
            [
                'App\\Entity\\Article',
                'create',
                [],
                'article/create.html.twig',
                [],
            ],
            [
                'App\\Entity\\Article',
                'create',
                [
                    'view' => [],
                ],
                'article/create.html.twig',
                [],
            ],
            [
                'App\\Entity\\Article',
                'create',
                [
                    'view' => [
                        'path' => 'foo/bar.baz',
                    ],
                ],
                'foo/bar.baz',
                [],
            ],
        ];
    }

    /**
     * @param class-string          $entityClass
     * @param non-empty-string      $action
     * @param array<string, mixed>  $actionConfig
     * @param class-string          $viewPath
     * @param array<string, string> $viewVariables
     */
    #[DataProvider('dataProviderClassActionAndViewPath')]
    public function testLoad(
        string $entityClass,
        string $action,
        array $actionConfig,
        string $viewPath,
        array $viewVariables,
    ): void {
        $schemaConfiguration = new SchemaConfiguration(
            new SchemaRouteConfiguration('foo', SchemaRoutePathsCollection::createEmpty()),
            new SchemaViewConfiguration('bar', SchemaViewVariablesCollection::createEmpty()),
        );

        $result = $this->viewConfigurationLoader->load($schemaConfiguration, $entityClass, $action, $actionConfig);

        self::assertNotNull($result);
        self::assertSame($viewPath, $result->getPath());
        self::assertSame($viewVariables, $result->getVariables()->all());
    }
}
