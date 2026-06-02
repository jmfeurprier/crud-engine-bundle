<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Configuration;

use Jmf\CrudEngine\Configuration\ActionConfigurationRepository;
use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Form\FormFallbackMode;
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
                        'formTypeClass'      => ArticleType::class,
                        'formSuggestedClass' => 'App\\Form\\Article\\CreateType',
                        'formFallback'       => 'provide',
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
                            'fallback'  => 'provide',
                        ],
                    ],
                    'index'  => [
                        'formTypeClass'      => null,
                        'formSuggestedClass' => 'App\\Form\\Article\\IndexType',
                        'formFallback'       => 'fail',
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
        $actionConfiguration = $this->createRepository()->get(Article::class, 'create');

        self::assertSame(Article::class, $actionConfiguration->getEntityClass());
        self::assertSame('create', $actionConfiguration->getAction());
        self::assertNull($actionConfiguration->getHelperClass());

        $formConfiguration = $actionConfiguration->getFormConfiguration();
        self::assertSame(ArticleType::class, $formConfiguration->getFormTypeClass());
        self::assertSame('App\\Form\\Article\\CreateType', $formConfiguration->getSuggestedFormTypeClass());
        self::assertSame(FormFallbackMode::PROVIDE, $formConfiguration->getFormFallbackMode());

        $redirectionConfiguration = $actionConfiguration->getRedirectionConfiguration();
        self::assertSame('article.read', $redirectionConfiguration->getRoute());
        self::assertSame(['id' => '{{ _entity.id }}'], $redirectionConfiguration->getParameters());
        self::assertNull($redirectionConfiguration->getFragment());

        $routeConfiguration = $actionConfiguration->getRouteConfiguration();
        self::assertSame('article.create', $routeConfiguration->getName());
        self::assertSame('articles/create', $routeConfiguration->getPath());
        self::assertSame(['id' => '\d+'], $routeConfiguration->getRequirements());

        $viewConfiguration = $actionConfiguration->getViewConfiguration();
        self::assertSame('article/create.html.twig', $viewConfiguration->getPath());
        self::assertSame(['form' => ['articleForm']], $viewConfiguration->getVariables());
        self::assertSame(ViewFallbackMode::PROVIDE, $viewConfiguration->getViewFallbackMode());
    }

    public function testIndexHasNoRedirectionAndFailFallback(): void
    {
        $actionConfiguration = $this->createRepository()->get(Article::class, 'index');

        self::assertSame(ViewFallbackMode::FAIL, $actionConfiguration->getViewConfiguration()->getViewFallbackMode());
        self::assertSame(FormFallbackMode::FAIL, $actionConfiguration->getFormConfiguration()->getFormFallbackMode());

        $this->expectException(CrudEngineMissingConfigurationException::class);

        $actionConfiguration->getRedirectionConfiguration();
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
