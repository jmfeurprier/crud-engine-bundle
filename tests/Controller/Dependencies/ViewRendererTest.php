<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Controller\Dependencies;

use Jmf\CrudEngine\Exception\CrudEngineMissingViewException;
use Jmf\CrudEngine\Form\FormFallbackMode;
use Jmf\CrudEngine\Model\ActionDefinition;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Registry\FormDefinition;
use Jmf\CrudEngine\Registry\RedirectionDefinition;
use Jmf\CrudEngine\Registry\RouteDefinition;
use Jmf\CrudEngine\Registry\ViewDefinition;
use Jmf\CrudEngine\View\ViewFallbackMode;
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
            $this->givenActionDefinition('article/read.html.twig', ViewFallbackMode::PROVIDE),
            [],
            ['entity' => new stdClass()],
        );

        self::assertSame('APP-READ', $response->getContent());
    }

    public function testRendersBuiltInTemplateWhenConfiguredTemplateMissing(): void
    {
        $response = $this->viewRenderer->render(
            $this->givenActionDefinition('article/missing.html.twig', ViewFallbackMode::PROVIDE),
            [],
            ['entity' => new stdClass()],
        );

        self::assertSame('BUILTIN-READ', $response->getContent());
    }

    public function testThrowsWhenConfiguredTemplateMissingAndFallbackIsError(): void
    {
        $this->expectException(CrudEngineMissingViewException::class);

        $this->viewRenderer->render(
            $this->givenActionDefinition('article/missing.html.twig', ViewFallbackMode::FAIL),
            [],
            ['entity' => new stdClass()],
        );
    }

    private function givenActionDefinition(
        string $viewPath,
        ViewFallbackMode $fallback,
    ): ActionDefinition {
        return new ActionDefinition(
            entityAction:             new EntityAction(
                                          stdClass::class,
                                          'read',
                                      ),
            helperClass:              null,
            formConfiguration:        new FormDefinition(
                                          formTypeClass:          null,
                                          suggestedFormTypeClass: 'StubFormType',
                                          formFallbackMode:       FormFallbackMode::PROVIDE,
                                      ),
            redirectionConfiguration: new RedirectionDefinition(
                                          route:      '',
                                          parameters: [],
                                      ),
            routeConfiguration:       new RouteDefinition(
                                          name:         '',
                                          path:         '',
                                          requirements: [],
                                      ),
            viewConfiguration:        new ViewDefinition(
                                          path:             $viewPath,
                                          variables:        [],
                                          viewFallbackMode: $fallback,
                                      ),
        );
    }
}
