<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Controller\Dependencies;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Form\ActionFormConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Form\FormFallbackMode;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\ActionRouteConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ViewFallbackMode;
use Jmf\CrudEngine\Controller\Dependencies\FormCreator;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Form\CrudEngineEntityType;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use Jmf\CrudEngine\Tests\Fixtures\ArticleType;
use PHPUnit\Framework\TestCase;
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
            $this->givenActionConfiguration(ArticleType::class, FormFallbackMode::PROVIDE),
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
                    'entity_class'              => Article::class,
                    'suggested_form_type_class' => 'StubFormType',
                ],
            )
            ->willReturn($form)
        ;

        $result = (new FormCreator($formFactory))->create(
            $this->givenActionConfiguration(null, FormFallbackMode::PROVIDE),
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
            $this->givenActionConfiguration(null, FormFallbackMode::FAIL),
            new Article(),
        );
    }

    /**
     * @param null|class-string<\Symfony\Component\Form\FormTypeInterface> $formTypeClass
     */
    private function givenActionConfiguration(
        ?string $formTypeClass,
        FormFallbackMode $formFallbackMode,
    ): ActionConfiguration {
        return new ActionConfiguration(
            entityClass:              Article::class,
            action:                   'create',
            helperClass:              null,
            formConfiguration:        new ActionFormConfiguration(
                                          formTypeClass:    $formTypeClass,
                                          suggestedFormTypeClass: 'StubFormType',
                                          formFallbackMode: $formFallbackMode,

                                      ),
            redirectionConfiguration: null,
            routeConfiguration:       new ActionRouteConfiguration('', '', []),
            viewConfiguration:        new ActionViewConfiguration('', [], ViewFallbackMode::PROVIDE),
        );
    }
}
