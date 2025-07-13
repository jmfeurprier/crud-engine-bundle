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

    /**
     * @throws CrudEngineInvalidConfigurationException
     */
    public function testLoadDefault(): void
    {
        $schemaConfig = [];

        $keySchema = $this->keySchemaLoader->load($schemaConfig);

        $this->assertNotEmpty(
            $keySchema->expand(
                $this->schemaValueExpander,
                [
                    'entityClass' => 'App\\Entity\\ArticleCategory',
                    'action'      => 'do_something',
                ],
            ),
        );
    }

    /**
     * @throws CrudEngineInvalidConfigurationException
     */
    public function testLoadWithEmptyConfig(): void
    {
        $schemaConfig = [
            'keys' => [],
        ];

        $keySchema = $this->keySchemaLoader->load($schemaConfig);

        $this->assertNotEmpty(
            $keySchema->expand(
                $this->schemaValueExpander,
                [
                    'entityClass' => 'App\\Entity\\ArticleCategory',
                    'action'      => 'do_something',
                ],
            ),
        );
    }

    /**
     * @throws CrudEngineInvalidConfigurationException
     */
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

        $this->assertArrayHasKey('foo', $expanded);
        $this->assertSame('bar', $expanded['foo']);
        $this->assertArrayHasKey('baz', $expanded);
        $this->assertSame('qux', $expanded['baz']);
    }

    /**
     * @throws CrudEngineInvalidConfigurationException
     */
    public function testLoadDefaultExpansion(): void
    {
        $schemaConfig = [];

        $keySchema = $this->keySchemaLoader->load($schemaConfig);

        $this->assertSame(
            [
                'ActionKey'   => 'DoSomething',
                'ActionKeys'  => 'DoSomethings',
                'actionKey'   => 'doSomething',
                'actionKeys'  => 'doSomethings',
                'action_key'  => 'do_something',
                'action_keys' => 'do_somethings',
                'action-key'  => 'do-something',
                'action-keys' => 'do-somethings',
                'EntityKey'   => 'ArticleCategory',
                'EntityKeys'  => 'ArticleCategories',
                'entityKey'   => 'articleCategory',
                'entityKeys'  => 'articleCategories',
                'entity_key'  => 'article_category',
                'entity_keys' => 'article_categories',
                'entity-key'  => 'article-category',
                'entity-keys' => 'article-categories',
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
