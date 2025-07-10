<?php

namespace Jmf\CrudEngine\Tests\Configuration\Entities\Action\View\Variables;

use Jmf\CrudEngine\Configuration\Entities\Action\View\Variables\ActionViewVariablesResolver;
use Jmf\CrudEngine\Configuration\Schema\Route\SchemaRouteConfiguration;
use Jmf\CrudEngine\Configuration\Schema\SchemaConfiguration;
use Jmf\CrudEngine\Configuration\Schema\View\SchemaViewConfiguration;
use Jmf\CrudEngine\Configuration\Schema\View\Variables\SchemaViewVariablesCollection;
use Jmf\CrudEngine\Configuration\ValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\TemplateRendering\TemplateRenderer;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
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
            new ValueExpander(new TemplateRenderer($twigEnvironment)),
        );
    }

    /**
     * @return array{
     *     0: array<non-empty-string, iterable<non-empty-string>>,
     *     1: non-empty-string,
     *     2: non-empty-string,
     *     3: array<string, mixed>,
     *     4: array<string, mixed>,
     * }[]
     */
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

    /**
     * @param array<non-empty-string, iterable<non-empty-string>> $schemaVariables
     * @param class-string                                        $entityClass
     * @param non-empty-string                                    $action
     * @param array<string, mixed>                                $viewConfig
     * @param array<string, mixed>                                $expected
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws Exception
     */
    #[DataProvider('dataProvider')]
    public function testLoad(
        array $schemaVariables,
        string $entityClass,
        string $action,
        array $viewConfig,
        array $expected,
    ): void {
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
