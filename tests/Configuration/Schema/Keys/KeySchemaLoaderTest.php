<?php

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

class KeySchemaLoaderTest extends TestCase
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

        $result = $this->keySchemaLoader->load($schemaConfig);

        self::assertNotEmpty($result->expand($this->schemaValueExpander, []));
    }

    /**
     * @throws CrudEngineInvalidConfigurationException
     */
    public function testLoadWithEmptyConfig(): void
    {
        $schemaConfig = [
            'keys' => [],
        ];

        $result = $this->keySchemaLoader->load($schemaConfig);

        self::assertNotEmpty($result->expand($this->schemaValueExpander, []));
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

        $result = $this->keySchemaLoader->load($schemaConfig);

        $expanded = $result->expand($this->schemaValueExpander, []);

        self::assertArrayHasKey('foo', $expanded);
        self::assertSame($expanded['foo'], 'bar');
        self::assertArrayHasKey('baz', $expanded);
        self::assertSame($expanded['baz'], 'qux');
    }

    /**
     * @throws CrudEngineInvalidConfigurationException
     */
    public function testLoadDefaultExpansion(): void
    {
        $schemaConfig = [];

        $result = $this->keySchemaLoader->load($schemaConfig);

        self::assertSame(
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
            $result->expand($this->schemaValueExpander, [
                'entityClass' => 'App\\Entity\\ArticleCategory',
                'action'      => 'do_something',
            ]),
        );
    }
}
