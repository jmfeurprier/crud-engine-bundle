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

        $formConfiguration = $actionDefinition->getFormConfiguration();
        self::assertSame(ArticleType::class, $formConfiguration->getFormTypeClass());
        self::assertSame('App\\Form\\Article\\CreateType', $formConfiguration->getSuggestedFormTypeClass());
        self::assertSame(FallbackMode::PROVIDE, $formConfiguration->getFallbackMode());

        $redirectionConfiguration = $actionDefinition->getRedirectionConfiguration();
        self::assertSame('article.read', $redirectionConfiguration->getRoute());
        self::assertSame(['id' => '{{ _entity.id }}'], $redirectionConfiguration->getParameters());
        self::assertNull($redirectionConfiguration->getFragment());

        $routeConfiguration = $actionDefinition->getRouteConfiguration();
        self::assertSame('article.create', $routeConfiguration->getName());
        self::assertSame('articles/create', $routeConfiguration->getPath());
        self::assertSame(['id' => '\d+'], $routeConfiguration->getRequirements());

        $viewConfiguration = $actionDefinition->getViewConfiguration();
        self::assertSame('article/create.html.twig', $viewConfiguration->getPath());
        self::assertSame(['form' => ['articleForm']], $viewConfiguration->getVariables());
        self::assertSame(FallbackMode::PROVIDE, $viewConfiguration->getFallbackMode());
    }

    public function testHydratesIndexActionWithoutRedirection(): void
    {
        $actionDefinition = $this->hydrate()[Article::class]['index'];

        self::assertSame(FallbackMode::FAIL, $actionDefinition->getViewConfiguration()->getFallbackMode());

        $this->expectException(CrudEngineMissingConfigurationException::class);

        $actionDefinition->getRedirectionConfiguration();
    }

    public function testHydratesActionWithoutForm(): void
    {
        $actionDefinition = $this->hydrate()[Article::class]['index'];

        $this->expectException(CrudEngineMissingConfigurationException::class);

        $actionDefinition->getFormConfiguration();
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
