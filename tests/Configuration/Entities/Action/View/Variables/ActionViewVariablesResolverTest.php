<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Configuration\Entities\Action\View\Variables;

use Jmf\CrudEngine\Configuration\Entities\Action\View\Variables\ActionViewVariablesResolver;
use Jmf\CrudEngine\Configuration\Schema\FormType\FormTypeSchema;
use Jmf\CrudEngine\Configuration\Schema\Helper\HelperSchema;
use Jmf\CrudEngine\Configuration\Schema\Keys\KeySchema;
use Jmf\CrudEngine\Configuration\Schema\Route\RouteSchema;
use Jmf\CrudEngine\Configuration\Schema\Schema;
use Jmf\CrudEngine\Configuration\Schema\View\Variables\ViewVariablesSchema;
use Jmf\CrudEngine\Configuration\Schema\View\ViewSchema;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\TemplateRendering\TemplateRenderer;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

final class ActionViewVariablesResolverTest extends TestCase
{
    private ActionViewVariablesResolver $actionViewVariablesResolver;

    #[Override]
    protected function setUp(): void
    {
        $twigEnvironment = new Environment(new ArrayLoader());
        $twigEnvironment->addExtension(new StringExtension());

        $this->actionViewVariablesResolver = new ActionViewVariablesResolver(
            new SchemaValueExpander(new TemplateRenderer($twigEnvironment)),
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
        $schema = new Schema(
            $this->createMock(KeySchema::class),
            $this->createMock(FormTypeSchema::class),
            $this->createMock(HelperSchema::class),
            $this->createMock(RouteSchema::class),
            new ViewSchema(
                'foo',
                new ViewVariablesSchema($schemaVariables),
            ),
        );

        $actionViewVariablesCollection = $this->actionViewVariablesResolver->resolve(
            $schema,
            $entityClass,
            $action,
            $viewConfig,
        );

        $this->assertSame($expected, $actionViewVariablesCollection->all());
    }
}
