<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Registry;

use Jmf\CrudEngine\Configuration\Action\Form\FormFallbackMode;
use Jmf\CrudEngine\Configuration\Action\View\ViewFallbackMode;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Registry\ActionConfigurationHydrator;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use Jmf\CrudEngine\Tests\Fixtures\ArticleType;
use PHPUnit\Framework\TestCase;

final class ActionConfigurationHydratorTest extends TestCase
{
    public function testHydratesCreateAction(): void
    {
        $actionConfiguration = $this->hydrate()[Article::class]['create'];

        self::assertSame(Article::class, $actionConfiguration->getEntityAction()->getEntityClass());
        self::assertSame('create', $actionConfiguration->getEntityAction()->getAction());
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

    public function testHydratesIndexActionWithoutRedirection(): void
    {
        $actionConfiguration = $this->hydrate()[Article::class]['index'];

        self::assertSame(ViewFallbackMode::FAIL, $actionConfiguration->getViewConfiguration()->getViewFallbackMode());

        $this->expectException(CrudEngineMissingConfigurationException::class);

        $actionConfiguration->getRedirectionConfiguration();
    }

    public function testHydratesActionWithoutForm(): void
    {
        $actionConfiguration = $this->hydrate()[Article::class]['index'];

        $this->expectException(CrudEngineMissingConfigurationException::class);

        $actionConfiguration->getFormConfiguration();
    }

    /**
     * @return array<class-string, array<non-empty-string, \Jmf\CrudEngine\Model\ActionConfiguration>>
     */
    private function hydrate(): array
    {
        return (new ActionConfigurationHydrator())->hydrate(
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
