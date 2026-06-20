<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Redirection;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Definition\FallbackMode;
use Jmf\CrudEngine\Definition\FormDefinition;
use Jmf\CrudEngine\Definition\RedirectionDefinition;
use Jmf\CrudEngine\Definition\RouteDefinition;
use Jmf\CrudEngine\Definition\ViewDefinition;
use Jmf\CrudEngine\Exception\CrudEngineRedirectionException;
use Jmf\CrudEngine\Model\CrudAction;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Redirection\RedirectionGenerator;
use Jmf\CrudEngine\Tests\Fixtures\Article;
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
            $this->givenActionDefinition(),
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
            $this->givenActionDefinition(),
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

    private function givenActionDefinition(): ActionDefinition
    {
        return new ActionDefinition(
            entityAction:          new EntityAction(
                                       Article::class,
                                       CrudAction::Create,
                                   ),
            helperClass:           null,
            formDefinition:        new FormDefinition(null, 'StubFormType', FallbackMode::PROVIDE),
            redirectionDefinition: new RedirectionDefinition('article.index', []),
            routeDefinition:       new RouteDefinition('route.name', '', []),
            viewDefinition:        new ViewDefinition('', [], FallbackMode::PROVIDE),
        );
    }
}
