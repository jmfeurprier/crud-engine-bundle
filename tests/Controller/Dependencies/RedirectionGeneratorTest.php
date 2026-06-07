<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Controller\Dependencies;

use Jmf\CrudEngine\Exception\CrudEngineRedirectionException;
use Jmf\CrudEngine\Form\FormFallbackMode;
use Jmf\CrudEngine\Model\ActionConfiguration;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Redirection\RedirectionGenerator;
use Jmf\CrudEngine\Registry\ActionFormConfiguration;
use Jmf\CrudEngine\Registry\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Registry\ActionRouteConfiguration;
use Jmf\CrudEngine\Registry\ActionViewConfiguration;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use Jmf\CrudEngine\View\ViewFallbackMode;
use Jmf\TemplateRendering\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RedirectionGeneratorTest extends TestCase
{
    public function testGeneratesRedirectResponse(): void
    {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/articles');

        $response = $this->createGenerator($urlGenerator)->generate(
            $this->givenActionConfiguration(),
            new stdClass(),
        );

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/articles', $response->getTargetUrl());
    }

    public function testWrapsUrlGenerationFailure(): void
    {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willThrowException(new RuntimeException('boom'));

        $this->expectException(CrudEngineRedirectionException::class);

        $this->createGenerator($urlGenerator)->generate(
            $this->givenActionConfiguration(),
            new stdClass(),
        );
    }

    private function createGenerator(UrlGeneratorInterface $urlGenerator): RedirectionGenerator
    {
        return new RedirectionGenerator(
            $urlGenerator,
            $this->createStub(TemplateRendererInterface::class),
        );
    }

    private function givenActionConfiguration(): ActionConfiguration
    {
        return new ActionConfiguration(
            entityAction:             new EntityAction(
                                          Article::class,
                                          'create',
                                      ),
            helperClass:              null,
            formConfiguration:        new ActionFormConfiguration(null, 'StubFormType', FormFallbackMode::PROVIDE),
            redirectionConfiguration: new ActionRedirectionConfiguration('article.index', []),
            routeConfiguration:       new ActionRouteConfiguration('', '', []),
            viewConfiguration:        new ActionViewConfiguration('', [], ViewFallbackMode::PROVIDE),
        );
    }
}
