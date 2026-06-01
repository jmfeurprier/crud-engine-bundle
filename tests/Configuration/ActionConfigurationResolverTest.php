<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Configuration;

use Jmf\CrudEngine\Configuration\ActionConfigurationResolver;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use Jmf\CrudEngine\Tests\Fixtures\ArticleType;
use Jmf\TemplateRendering\TemplateRenderer;
use Override;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

final class ActionConfigurationResolverTest extends TestCase
{
    private ActionConfigurationResolver $resolver;

    #[Override]
    protected function setUp(): void
    {
        $twigEnvironment = new Environment(new ArrayLoader());
        $twigEnvironment->addExtension(new StringExtension());

        $this->resolver = new ActionConfigurationResolver(
            new SchemaValueExpander(new TemplateRenderer($twigEnvironment)),
        );
    }

    public function testResolvesDefaults(): void
    {
        $resolved = $this->resolver->resolve(
            [
                'entities' => [
                    Article::class => [
                        'actions' => [
                            'index'  => [],
                            'read'   => [],
                            'create' => [],
                            'update' => [],
                            'delete' => [],
                        ],
                    ],
                ],
            ],
        );

        $article = $resolved[Article::class];

        self::assertSame('article.index', $article['index']['route']['name']);
        self::assertSame('articles', $article['index']['route']['path']);
        self::assertSame('articles/{id}', $article['read']['route']['path']);
        self::assertSame('articles/create', $article['create']['route']['path']);
        self::assertSame('articles/{id}/update', $article['update']['route']['path']);
        self::assertSame('articles/{id}/delete', $article['delete']['route']['path']);

        self::assertSame('article/index.html.twig', $article['index']['view']['path']);
        self::assertSame('built_in', $article['index']['view']['fallback']);
        self::assertSame([], $article['index']['view']['variables']);

        self::assertNull($article['index']['formTypeClass']);
        self::assertSame('generic', $article['index']['formFallback']);
        self::assertNull($article['index']['helperClass']);

        self::assertNull($article['index']['redirection']);
        self::assertNull($article['read']['redirection']);
        self::assertSame(
            [
                'route'      => 'article.read',
                'parameters' => ['id' => '{{ _entity.id }}'],
                'fragment'   => null,
            ],
            $article['create']['redirection'],
        );
        self::assertSame(
            [
                'route'      => 'article.index',
                'parameters' => [],
                'fragment'   => null,
            ],
            $article['delete']['redirection'],
        );
    }

    public function testActionOverridesWin(): void
    {
        $resolved = $this->resolver->resolve(
            [
                'entities' => [
                    Article::class => [
                        'actions' => [
                            'create' => [
                                'formType'    => ArticleType::class,
                                'route'       => [
                                    'path'         => '/blog/new',
                                    'requirements' => ['id' => '\d+'],
                                ],
                                'view'        => [
                                    'path'      => 'blog/new.html.twig',
                                    'variables' => ['form' => ['articleForm', 'newArticleForm']],
                                ],
                                'redirection' => [
                                    'route'      => 'blog.index',
                                    'parameters' => ['id' => '{{ _entity.id }}'],
                                    'fragment'   => 'section',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        );

        $create = $resolved[Article::class]['create'];

        self::assertSame(ArticleType::class, $create['formTypeClass']);
        self::assertSame('/blog/new', $create['route']['path']);
        self::assertSame(['id' => '\d+'], $create['route']['requirements']);
        self::assertSame('blog/new.html.twig', $create['view']['path']);
        self::assertSame(['form' => ['articleForm', 'newArticleForm']], $create['view']['variables']);
        self::assertSame(
            [
                'route'      => 'blog.index',
                'parameters' => ['id' => '{{ _entity.id }}'],
                'fragment'   => 'section',
            ],
            $create['redirection'],
        );
    }

    public function testSchemaPathOverrideMergesWithDefaults(): void
    {
        $resolved = $this->resolver->resolve(
            [
                'schema'   => [
                    'route' => [
                        'name'  => '{{ entity_key }}_{{ action_key }}',
                        'paths' => ['index' => 'custom-index'],
                    ],
                    'view'  => [
                        'fallback' => 'fail',
                    ],
                ],
                'entities' => [
                    Article::class => [
                        'actions' => [
                            'index' => [],
                            'read'  => [],
                        ],
                    ],
                ],
            ],
        );

        $article = $resolved[Article::class];

        // Overridden path for index, default kept for read (merge, not replace).
        self::assertSame('custom-index', $article['index']['route']['path']);
        self::assertSame('articles/{id}', $article['read']['route']['path']);
        // Overridden route-name pattern applies to all actions.
        self::assertSame('article_index', $article['index']['route']['name']);
        // Global fallback override.
        self::assertSame('fail', $article['index']['view']['fallback']);
    }

    public function testResolvesFormFallbackOverride(): void
    {
        $resolved = $this->resolver->resolve(
            [
                'schema'   => [
                    'form' => [
                        'fallback' => 'fail',
                    ],
                ],
                'entities' => [
                    Article::class => [
                        'actions' => [
                            'create' => [],
                        ],
                    ],
                ],
            ],
        );

        self::assertSame('fail', $resolved[Article::class]['create']['formFallback']);
    }
}
