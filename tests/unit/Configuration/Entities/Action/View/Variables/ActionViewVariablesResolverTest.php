<?php

namespace Jmf\CrudEngine\Tests\Configuration\Entities\Action\View\Variables;

use Jmf\CrudEngine\Configuration\Entities\Action\View\Variables\ActionViewVariablesResolver;
use Jmf\CrudEngine\Configuration\Schema\Route\SchemaRouteConfiguration;
use Jmf\CrudEngine\Configuration\Schema\SchemaConfiguration;
use Jmf\CrudEngine\Configuration\Schema\View\SchemaViewConfiguration;
use Jmf\CrudEngine\Configuration\Schema\View\Variables\SchemaViewVariablesCollection;
use Jmf\TemplateRendering\TemplateRenderer;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

class ActionViewVariablesResolverTest extends TestCase
{
    private ActionViewVariablesResolver $actionViewVariablesResolver;

    #[Override]
    protected function setUp(): void
    {
        $twigEnvironment = new Environment(new ArrayLoader());
        $twigEnvironment->addExtension(new StringExtension());

        $this->actionViewVariablesResolver = new ActionViewVariablesResolver(
            new TemplateRenderer($twigEnvironment),
        );
    }

    public static function dataProvider(): iterable
    {
        return [
            [
                [],
                'App\\Entity\\Article',
                'update',
                [],
                [],
            ],
            [
                [
                    'entity' => [
                        '_entity_',
                    ],
                ],
                'App\\Entity\\Article',
                'update',
                [],
                [
                    'entity' => [
                        '_entity_',
                    ],
                ],
            ],
            [
                [
                    'foo' => [
                        '{{ entityClass|u.afterLast("\\\\").camel }}{{ action|title }}',
                    ],
                ],
                'App\\Entity\\Article',
                'update',
                [],
                [
                    'foo' => [
                        'articleUpdate',
                    ],
                ],
            ],
            [
                [
                    'entity' => [
                        '_foo_',
                        '_bar_',
                    ],
                ],
                'App\\Entity\\Article',
                'update',
                [],
                [
                    'entity' => [
                        '_foo_',
                        '_bar_',
                    ],
                ],
            ],
            [
                [
                    'entity' => [
                        '_foo_',
                        '_bar_',
                    ],
                ],
                'App\\Entity\\Article',
                'update',
                [
                    'variables' => [
                        'foo' => 'bar',
                    ],
                ],
                [
                    'entity' => [
                        '_foo_',
                        '_bar_',
                    ],
                    'foo'    => [
                        'bar',
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('dataProvider')]
    public function testLoad(
        array $schemaVariables,
        string $entityClass,
        string $action,
        array $viewConfig,
        array $expected,
    ) {
        $schemaConfiguration = new SchemaConfiguration(
            $this->createMock(SchemaRouteConfiguration::class),
            new SchemaViewConfiguration(
                'foo',
                new SchemaViewVariablesCollection($schemaVariables),
            ),
        );

        $result = $this->actionViewVariablesResolver->resolve(
            $schemaConfiguration,
            $entityClass,
            $action,
            $viewConfig,
        );

        self::assertSame($expected, $result->all());
    }
}
