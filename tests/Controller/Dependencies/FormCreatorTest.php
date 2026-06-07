<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Controller\Dependencies;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Definition\FallbackMode;
use Jmf\CrudEngine\Definition\FormDefinition;
use Jmf\CrudEngine\Definition\RouteDefinition;
use Jmf\CrudEngine\Definition\ViewDefinition;
use Jmf\CrudEngine\Exception\CrudEngineFormCreationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Form\CrudEngineEntityType;
use Jmf\CrudEngine\Form\FormCreator;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use Jmf\CrudEngine\Tests\Fixtures\ArticleType;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

final class FormCreatorTest extends TestCase
{
    public function testUsesConfiguredFormType(): void
    {
        $article = new Article();
        $form    = $this->createStub(FormInterface::class);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory
            ->expects(self::once())
            ->method('create')
            ->with(ArticleType::class, $article)
            ->willReturn($form)
        ;

        $result = (new FormCreator($formFactory))->create(
            $this->givenActionDefinition(ArticleType::class, FallbackMode::PROVIDE),
            $article,
        );

        self::assertSame($form, $result);
    }

    public function testFallsBackToGenericTypeWhenNoFormType(): void
    {
        $article = new Article();
        $form    = $this->createStub(FormInterface::class);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory
            ->expects(self::once())
            ->method('create')
            ->with(
                CrudEngineEntityType::class,
                $article,
                [
                    'entity_action'             => new EntityAction(Article::class, 'create'),
                    'suggested_form_type_class' => 'StubFormType',
                ],
            )
            ->willReturn($form)
        ;

        $result = (new FormCreator($formFactory))->create(
            $this->givenActionDefinition(null, FallbackMode::PROVIDE),
            $article,
        );

        self::assertSame($form, $result);
    }

    public function testThrowsWhenNoFormTypeAndFallbackIsFail(): void
    {
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(self::never())->method('create');

        $this->expectException(CrudEngineMissingConfigurationException::class);

        (new FormCreator($formFactory))->create(
            $this->givenActionDefinition(null, FallbackMode::FAIL),
            new Article(),
        );
    }

    public function testWrapsFormFactoryFailure(): void
    {
        $formFactory = $this->createStub(FormFactoryInterface::class);
        $formFactory->method('create')->willThrowException(new RuntimeException('boom'));

        $this->expectException(CrudEngineFormCreationException::class);

        (new FormCreator($formFactory))->create(
            $this->givenActionDefinition(ArticleType::class, FallbackMode::PROVIDE),
            new Article(),
        );
    }

    /**
     * @param null|class-string<\Symfony\Component\Form\FormTypeInterface> $formTypeClass
     */
    private function givenActionDefinition(
        ?string $formTypeClass,
        FallbackMode $formFallbackMode,
    ): ActionDefinition {
        return new ActionDefinition(
            entityAction:             new EntityAction(
                                          Article::class,
                                          'create',
                                      ),
            helperClass:              null,
            formDefinition:        new FormDefinition(
                                          formTypeClass:          $formTypeClass,
                                          suggestedFormTypeClass: 'StubFormType',
                                          fallbackMode:       $formFallbackMode,

                                      ),
            redirectionDefinition: null,
            routeDefinition:       new RouteDefinition('', '', []),
            viewDefinition:        new ViewDefinition('', [], FallbackMode::PROVIDE),
        );
    }
}
