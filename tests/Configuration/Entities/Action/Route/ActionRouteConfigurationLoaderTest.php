<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Configuration\Entities\Action\Route;

use Jmf\CrudEngine\Configuration\Entities\Action\Route\ActionRouteConfigurationLoader;
use Jmf\CrudEngine\Configuration\Schema\Route\Paths\RoutePathSchema;
use Jmf\CrudEngine\Configuration\Schema\Route\RouteSchema;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\TemplateRendering\TemplateRenderer;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

final class ActionRouteConfigurationLoaderTest extends TestCase
{
    private ActionRouteConfigurationLoader $actionRouteConfigurationLoader;

    #[Override]
    protected function setUp(): void
    {
        $twigEnvironment = new Environment(new ArrayLoader());
        $twigEnvironment->addExtension(new StringExtension());

        $this->actionRouteConfigurationLoader = new ActionRouteConfigurationLoader(
            new SchemaValueExpander(new TemplateRenderer($twigEnvironment)),
        );
    }

    /**
     * @return array{
     *     0: array<non-empty-string, non-empty-string>,
     *     1: non-empty-string,
     *     2: non-empty-string,
     *     3: array<string, mixed>,
     *     4: non-empty-string,
     *     5: non-empty-string,
     * }[]
     */
    public static function dataProviderClassActionAndRoutePath(): iterable
    {
        return [
            [
                [
                    'action_key'     => 'create',
                    'entity_key'     => 'article',
                    'entitydashkeys' => 'articles',
                ],
                'App\\Entity\\Article',
                'create',
                [],
                'article.create',
                'articles/create',
            ],
            [
                [
                    'action_key'     => 'read',
                    'entity_key'     => 'article',
                    'entitydashkeys' => 'articles',
                ],
                'App\\Entity\\Article',
                'read',
                [],
                'article.read',
                'articles/{id}',
            ],
            [
                [
                    'action_key'     => 'index',
                    'entity_key'     => 'article',
                    'entitydashkeys' => 'articles',
                ],
                'App\\Entity\\Article',
                'index',
                [],
                'article.index',
                'articles',
            ],
            [
                [
                    'action_key'     => 'update',
                    'entity_key'     => 'api_key',
                    'entitydashkeys' => 'api-keys',
                ],
                'App\\Entity\\ApiKey',
                'update',
                [],
                'api_key.update',
                'api-keys/{id}/update',
            ],
            [
                [
                    'action_key'     => 'update',
                    'entity_key'     => 'api_key',
                    'entitydashkeys' => 'api-keys',
                ],
                'App\\Entity\\ApiKey',
                'update',
                [
                    'route' => [
                        'path' => 'foo/bar',
                    ],
                ],
                'api_key.update',
                'foo/bar',
            ],
        ];
    }

    /**
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $actionConfig
     * @param non-empty-string                          $expectedRouteName
     * @param non-empty-string                          $expectedRoutePath
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    #[DataProvider('dataProviderClassActionAndRoutePath')]
    public function testLoad(
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
        string $expectedRouteName,
        string $expectedRoutePath,
    ): void {
        $routeSchema = new RouteSchema(
            RouteSchema::DEFAULT_NAME,
            RoutePathSchema::createDefault(),
        );

        $routeConfiguration = $this->actionRouteConfigurationLoader->load(
            $routeSchema,
            $keys,
            $entityClass,
            $action,
            $actionConfig,
        );

        $this->assertSame($expectedRouteName, $routeConfiguration->getName());
        $this->assertSame($expectedRoutePath, $routeConfiguration->getPath());
    }
}
