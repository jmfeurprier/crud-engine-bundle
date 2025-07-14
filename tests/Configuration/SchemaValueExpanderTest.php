<?php

namespace Jmf\CrudEngine\Tests\Configuration;

use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\TemplateRendering\TemplateRenderer;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

class SchemaValueExpanderTest extends TestCase
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
     * @throws CrudEngineInvalidConfigurationException
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

        $this->assertSame($expected, $result);
    }
}
