<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Configuration;

use Jmf\CrudEngine\Configuration\ActionConfigurationRepository;
use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ViewFallbackMode;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use Jmf\CrudEngine\Tests\Fixtures\ArticleType;
use PHPUnit\Framework\TestCase;

final class ActionConfigurationRepositoryTest extends TestCase
{
    private function createRepository(): ActionConfigurationRepository
    {
        return new ActionConfigurationRepository(
            [
                Article::class => [
                    'create' => [
                        'formTypeClass' => ArticleType::class,
                        'helperClass'   => null,
                        'route'         => [
                            'name'         => 'article.create',
                            'path'         => 'articles/create',
                            'requirements' => ['id' => '\d+'],
                        ],
                        'redirection'   => [
                            'route'      => 'article.read',
                            'parameters' => ['id' => '{{ _entity.id }}'],
                            'fragment'   => null,
                        ],
                        'view'          => [
                            'path'      => 'article/create.html.twig',
                            'variables' => ['form' => ['articleForm']],
                            'fallback'  => 'built_in',
                        ],
                    ],
                    'index' => [
                        'formTypeClass' => null,
                        'helperClass'   => null,
                        'route'         => [
                            'name'         => 'article.index',
                            'path'         => 'articles',
                            'requirements' => [],
                        ],
                        'redirection'   => null,
                        'view'          => [
                            'path'      => 'article/index.html.twig',
                            'variables' => [],
                            'fallback'  => 'fail',
                        ],
                    ],
                ],
            ],
        );
    }

    public function testGetHydratesDtoGraph(): void
    {
        $configuration = $this->createRepository()->get(Article::class, 'create');

        self::assertInstanceOf(ActionConfiguration::class, $configuration);
        self::assertSame(Article::class, $configuration->getEntityClass());
        self::assertSame('create', $configuration->getAction());
        self::assertSame(ArticleType::class, $configuration->getFormTypeClass());
        self::assertNull($configuration->getHelperClass());

        $route = $configuration->getRouteConfiguration();
        self::assertSame('article.create', $route->getName());
        self::assertSame('articles/create', $route->getPath());
        self::assertSame(['id' => '\d+'], $route->getRequirements());

        $redirection = $configuration->getRedirectionConfiguration();
        self::assertSame('article.read', $redirection->getRoute());
        self::assertSame(['id' => '{{ _entity.id }}'], $redirection->getParameters());
        self::assertNull($redirection->getFragment());

        $view = $configuration->getViewConfiguration();
        self::assertSame('article/create.html.twig', $view->getPath());
        self::assertSame(['form' => ['articleForm']], $view->getVariables());
        self::assertSame(ViewFallbackMode::RENDER_BUILT_IN, $view->getViewFallbackMode());
    }

    public function testIndexHasNoRedirectionAndFailFallback(): void
    {
        $configuration = $this->createRepository()->get(Article::class, 'index');

        self::assertSame(ViewFallbackMode::FAIL, $configuration->getViewConfiguration()->getViewFallbackMode());

        $this->expectException(CrudEngineMissingConfigurationException::class);

        $configuration->getRedirectionConfiguration();
    }

    public function testTryGetReturnsNullForUnknown(): void
    {
        self::assertNull($this->createRepository()->tryGet(Article::class, 'unknown'));
    }

    public function testAllYieldsEveryConfiguration(): void
    {
        $all = iterator_to_array($this->createRepository()->all(), false);

        self::assertCount(2, $all);
        self::assertContainsOnlyInstancesOf(ActionConfiguration::class, $all);
    }
}
