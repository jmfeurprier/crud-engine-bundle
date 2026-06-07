<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Compilation;

use Jmf\CrudEngine\Compilation\Resolution\SchemaValueExpander;
use Jmf\TemplateRendering\TemplateRenderer;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

final class SchemaValueExpanderTest extends TestCase
{
    private SchemaValueExpander $schemaValueExpander;

    #[Override]
    protected function setUp(): void
    {
        $twigEnvironment = new Environment(new ArrayLoader());
        $twigEnvironment->addExtension(new StringExtension());

        $this->schemaValueExpander = new SchemaValueExpander(
            new TemplateRenderer($twigEnvironment),
        );
    }

    /**
     * @return array{
     *     0: string,
     *     1: array<non-empty-string, non-empty-string>,
     *     2: array<non-empty-string, mixed>,
     *     3: string,
     * }[]
     */
    public static function dataProvider(): iterable
    {
        return [
            [
                '',
                [],
                [],
                '',
            ],
            [
                'foo',
                [],
                [],
                'foo',
            ],
            [
                '{{ foo }}',
                [],
                [
                    'foo' => 'bar',
                ],
                'bar',
            ],
            [
                '{{ foo }}',
                [
                    'foo' => 'bar',
                ],
                [
                ],
                'bar',
            ],
            [
                '{{ foo }}',
                [
                    'foo' => 'bar',
                ],
                [
                    'foo' => 'baz',
                ],
                'baz',
            ],
        ];
    }

    /**
     * @param array<non-empty-string, non-empty-string> $keys
     * @param array<non-empty-string, mixed>            $arguments
     */
    #[DataProvider('dataProvider')]
    public function testExpand(
        string $value,
        array $keys,
        array $arguments,
        string $expected,
    ): void {
        $result = $this->schemaValueExpander->expand(
            $value,
            $keys,
            $arguments,
        );

        self::assertSame($expected, $result);
    }
}
