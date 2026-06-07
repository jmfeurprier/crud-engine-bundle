<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Registry;

use Jmf\CrudEngine\Definition\FallbackMode;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Registry\ActionDefinitionHydrator;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use Jmf\CrudEngine\Tests\Fixtures\ArticleType;
use PHPUnit\Framework\TestCase;

final class ActionDefinitionHydratorTest extends TestCase
{
    public function testHydratesCreateAction(): void
    {
        $actionDefinition = $this->hydrate()[Article::class]['create'];

        self::assertSame(Article::class, $actionDefinition->getEntityAction()->getEntityClass());
        self::assertSame('create', $actionDefinition->getEntityAction()->getAction());
        self::assertNull($actionDefinition->getHelperClass());

        $formDefinition = $actionDefinition->getFormDefinition();
        self::assertSame(ArticleType::class, $formDefinition->getFormTypeClass());
        self::assertSame('App\\Form\\Article\\CreateType', $formDefinition->getSuggestedFormTypeClass());
        self::assertSame(FallbackMode::PROVIDE, $formDefinition->getFallbackMode());

        $redirectionDefinition = $actionDefinition->getRedirectionDefinition();
        self::assertSame('article.read', $redirectionDefinition->getRoute());
        self::assertSame(['id' => '{{ _entity.id }}'], $redirectionDefinition->getParameters());
        self::assertNull($redirectionDefinition->getFragment());

        $routeDefinition = $actionDefinition->getRouteDefinition();
        self::assertSame('article.create', $routeDefinition->getName());
        self::assertSame('articles/create', $routeDefinition->getPath());
        self::assertSame(['id' => '\d+'], $routeDefinition->getRequirements());

        $viewDefinition = $actionDefinition->getViewDefinition();
        self::assertSame('article/create.html.twig', $viewDefinition->getPath());
        self::assertSame(['form' => ['articleForm']], $viewDefinition->getVariables());
        self::assertSame(FallbackMode::PROVIDE, $viewDefinition->getFallbackMode());
    }

    public function testHydratesIndexActionWithoutRedirection(): void
    {
        $actionDefinition = $this->hydrate()[Article::class]['index'];

        self::assertSame(FallbackMode::FAIL, $actionDefinition->getViewDefinition()->getFallbackMode());

        $this->expectException(CrudEngineMissingConfigurationException::class);

        $actionDefinition->getRedirectionDefinition();
    }

    public function testHydratesActionWithoutForm(): void
    {
        $actionDefinition = $this->hydrate()[Article::class]['index'];

        $this->expectException(CrudEngineMissingConfigurationException::class);

        $actionDefinition->getFormDefinition();
    }

    /**
     * @return array<class-string, array<non-empty-string, \Jmf\CrudEngine\Definition\ActionDefinition>>
     */
    private function hydrate(): array
    {
        return (new ActionDefinitionHydrator())->hydrate(
            [
                Article::class => [
                    'create' => [
                        'form'        => [
                            'typeClass'      => ArticleType::class,
                            'suggestedClass' => 'App\\Form\\Article\\CreateType',
                            'fallback'       => 'provide',
                        ],
                        'helperClass' => null,
                        'route'       => [
                            'name'         => 'article.create',
                            'path'         => 'articles/create',
                            'requirements' => ['id' => '\d+'],
                        ],
                        'redirection' => [
                            'route'      => 'article.read',
                            'parameters' => ['id' => '{{ _entity.id }}'],
                            'fragment'   => null,
                        ],
                        'view'        => [
                            'path'      => 'article/create.html.twig',
                            'variables' => ['form' => ['articleForm']],
                            'fallback'  => 'provide',
                        ],
                    ],
                    'index'  => [
                        'form'        => null,
                        'helperClass' => null,
                        'route'       => [
                            'name'         => 'article.index',
                            'path'         => 'articles',
                            'requirements' => [],
                        ],
                        'redirection' => null,
                        'view'        => [
                            'path'      => 'article/index.html.twig',
                            'variables' => [],
                            'fallback'  => 'fail',
                        ],
                    ],
                ],
            ],
        );
    }
}
