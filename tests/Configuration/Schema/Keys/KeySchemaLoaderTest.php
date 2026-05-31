<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Configuration\Schema\Keys;

use Jmf\CrudEngine\Configuration\Schema\Keys\KeySchemaLoader;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\TemplateRendering\TemplateRenderer;
use Override;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

final class KeySchemaLoaderTest extends TestCase
{
    private KeySchemaLoader $keySchemaLoader;

    private SchemaValueExpander $schemaValueExpander;

    #[Override]
    protected function setUp(): void
    {
        $this->keySchemaLoader = new KeySchemaLoader();

        $twigEnvironment = new Environment(new ArrayLoader());
        $twigEnvironment->addExtension(new StringExtension());

        $this->schemaValueExpander = new SchemaValueExpander(
            new TemplateRenderer($twigEnvironment),
        );
    }

    public function testLoadDefault(): void
    {
        $schemaConfig = [];

        $keySchema = $this->keySchemaLoader->load($schemaConfig);

        self::assertNotEmpty(
            $keySchema->expand(
                $this->schemaValueExpander,
                [
                    'entityClass' => 'App\\Entity\\ArticleCategory',
                    'action'      => 'do_something',
                ],
            ),
        );
    }

    public function testLoadWithEmptyConfig(): void
    {
        $schemaConfig = [
            'keys' => [],
        ];

        $keySchema = $this->keySchemaLoader->load($schemaConfig);

        self::assertNotEmpty(
            $keySchema->expand(
                $this->schemaValueExpander,
                [
                    'entityClass' => 'App\\Entity\\ArticleCategory',
                    'action'      => 'do_something',
                ],
            ),
        );
    }

    public function testLoad(): void
    {
        $schemaConfig = [
            'keys' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ];

        $keySchema = $this->keySchemaLoader->load($schemaConfig);

        $expanded = $keySchema->expand(
            $this->schemaValueExpander,
            [
                'entityClass' => 'App\\Entity\\ArticleCategory',
                'action'      => 'do_something',
            ],
        );

        self::assertArrayHasKey('foo', $expanded);
        self::assertSame('bar', $expanded['foo']);
        self::assertArrayHasKey('baz', $expanded);
        self::assertSame('qux', $expanded['baz']);
    }

    public function testLoadDefaultExpansion(): void
    {
        $schemaConfig = [];

        $keySchema = $this->keySchemaLoader->load($schemaConfig);

        self::assertSame(
            [
                'ActionKey'      => 'DoSomething',
                'ActionKeys'     => 'DoSomethings',
                'actionKey'      => 'doSomething',
                'actionKeys'     => 'doSomethings',
                'action_key'     => 'do_something',
                'action_keys'    => 'do_somethings',
                'actiondashkey'     => 'do-something',
                'actiondashkeys'    => 'do-somethings',
                'EntityKey'      => 'ArticleCategory',
                'EntityKeys'     => 'ArticleCategories',
                'entityKey'      => 'articleCategory',
                'entityKeys'     => 'articleCategories',
                'entity_key'     => 'article_category',
                'entity_keys'    => 'article_categories',
                'entitydashkey'  => 'article-category',
                'entitydashkeys' => 'article-categories',
            ],
            $keySchema->expand(
                $this->schemaValueExpander,
                [
                    'entityClass' => 'App\\Entity\\ArticleCategory',
                    'action'      => 'do_something',
                ],
            ),
        );
    }
}
