<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Controller\Dependencies;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Form\ActionFormConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Form\FormFallbackMode;
use Jmf\CrudEngine\Configuration\Entities\Action\Redirection\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\ActionRouteConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ViewFallbackMode;
use Jmf\CrudEngine\Controller\Dependencies\ViewRenderer;
use Jmf\CrudEngine\Exception\CrudEngineMissingViewException;
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
            $this->givenActionConfiguration('article/read.html.twig', ViewFallbackMode::PROVIDE),
            [],
            ['entity' => new stdClass()],
        );

        self::assertSame('APP-READ', $response->getContent());
    }

    public function testRendersBuiltInTemplateWhenConfiguredTemplateMissing(): void
    {
        $response = $this->viewRenderer->render(
            $this->givenActionConfiguration('article/missing.html.twig', ViewFallbackMode::PROVIDE),
            [],
            ['entity' => new stdClass()],
        );

        self::assertSame('BUILTIN-READ', $response->getContent());
    }

    public function testThrowsWhenConfiguredTemplateMissingAndFallbackIsError(): void
    {
        $this->expectException(CrudEngineMissingViewException::class);

        $this->viewRenderer->render(
            $this->givenActionConfiguration('article/missing.html.twig', ViewFallbackMode::FAIL),
            [],
            ['entity' => new stdClass()],
        );
    }

    private function givenActionConfiguration(
        string $viewPath,
        ViewFallbackMode $fallback,
    ): ActionConfiguration {
        return new ActionConfiguration(
            entityClass:              stdClass::class,
            action:                   'read',
            helperClass:              null,
            formConfiguration:        new ActionFormConfiguration(
                                          formTypeClass:    null,
                                          suggestedFormTypeClass: 'StubFormType',
                                          formFallbackMode: FormFallbackMode::PROVIDE,
                                      ),
            redirectionConfiguration: new ActionRedirectionConfiguration(
                                          route:      '',
                                          parameters: [],
                                      ),
            routeConfiguration:       new ActionRouteConfiguration(
                                          name:         '',
                                          path:         '',
                                          requirements: [],
                                      ),
            viewConfiguration:        new ActionViewConfiguration(
                                          path:             $viewPath,
                                          variables:        [],
                                          viewFallbackMode: $fallback,
                                      ),
        );
    }
}
