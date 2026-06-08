<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\View;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Definition\FallbackMode;
use Jmf\CrudEngine\Definition\FormDefinition;
use Jmf\CrudEngine\Definition\RedirectionDefinition;
use Jmf\CrudEngine\Definition\RouteDefinition;
use Jmf\CrudEngine\Definition\ViewDefinition;
use Jmf\CrudEngine\Exception\CrudEngineMissingViewException;
use Jmf\CrudEngine\Model\CrudAction;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\View\ViewRenderer;
use Jmf\TemplateRendering\TemplateRenderer;
use Override;
use PHPUnit\Framework\TestCase;
use stdClass;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class ViewRendererTest extends TestCase
{
    private ViewRenderer $viewRenderer;

    #[Override]
    protected function setUp(): void
    {
        $twigEnvironment = new Environment(
            new ArrayLoader(
                [
                    'article/read.html.twig'        => 'APP-READ',
                    '@JmfCrudEngine/read.html.twig' => 'BUILTIN-READ',
                ],
            ),
        );

        $this->viewRenderer = new ViewRenderer(
            new TemplateRenderer($twigEnvironment),
            $twigEnvironment,
        );
    }

    public function testRendersConfiguredTemplateWhenItExists(): void
    {
        $response = $this->viewRenderer->render(
            $this->givenActionDefinition('article/read.html.twig', FallbackMode::PROVIDE),
            [],
            ['entity' => new stdClass()],
        );

        self::assertSame('APP-READ', $response->getContent());
    }

    public function testRendersBuiltInTemplateWhenConfiguredTemplateMissing(): void
    {
        $response = $this->viewRenderer->render(
            $this->givenActionDefinition('article/missing.html.twig', FallbackMode::PROVIDE),
            [],
            ['entity' => new stdClass()],
        );

        self::assertSame('BUILTIN-READ', $response->getContent());
    }

    public function testThrowsWhenConfiguredTemplateMissingAndFallbackIsError(): void
    {
        $this->expectException(CrudEngineMissingViewException::class);

        $this->viewRenderer->render(
            $this->givenActionDefinition('article/missing.html.twig', FallbackMode::FAIL),
            [],
            ['entity' => new stdClass()],
        );
    }

    private function givenActionDefinition(
        string $viewPath,
        FallbackMode $fallback,
    ): ActionDefinition {
        return new ActionDefinition(
            entityAction:          new EntityAction(
                                       stdClass::class,
                                       CrudAction::Read,
                                   ),
            helperClass:           null,
            formDefinition:        new FormDefinition(
                                       formTypeClass:          null,
                                       suggestedFormTypeClass: 'StubFormType',
                                       fallbackMode:           FallbackMode::PROVIDE,
                                   ),
            redirectionDefinition: new RedirectionDefinition(
                                       route:      '',
                                       parameters: [],
                                   ),
            routeDefinition:       new RouteDefinition(
                                       name:         '',
                                       path:         '',
                                       requirements: [],
                                   ),
            viewDefinition:        new ViewDefinition(
                                       path:         $viewPath,
                                       variables:    [],
                                       fallbackMode: $fallback,
                                   ),
        );
    }
}
