<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Controller;

use Jmf\CrudEngine\Controller\CreateAction;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\CreateActionHelperInterface;
use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Definition\FallbackMode;
use Jmf\CrudEngine\Definition\FormDefinition;
use Jmf\CrudEngine\Definition\RedirectionDefinition;
use Jmf\CrudEngine\Definition\RouteDefinition;
use Jmf\CrudEngine\Definition\ViewDefinition;
use Jmf\CrudEngine\Form\FormCreator;
use Jmf\CrudEngine\Model\CrudAction;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Persistence\EntityManagerResolver;
use Jmf\CrudEngine\Redirection\RedirectionGenerator;
use Jmf\CrudEngine\Registry\ActionDefinitionRegistryInterface;
use Jmf\CrudEngine\View\ViewRenderer;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CreateActionTest extends TestCase
{
    private ActionDefinitionRegistryInterface & Stub $actionDefinitionRegistry;

    private ActionHelperResolver & Stub $actionHelperResolver;

    /**
     * @var CreateActionHelperInterface<stdClass> & Stub
     */
    private CreateActionHelperInterface & Stub $defaultActionHelper;

    private FormCreator & Stub $formCreator;

    private EntityManagerResolver & Stub $objectManagerResolver;

    private RedirectionGenerator & Stub $redirectionGenerator;

    private ViewRenderer & Stub $viewRenderer;

    #[Override]
    protected function setUp(): void
    {
        $this->actionDefinitionRegistry = $this->createStub(ActionDefinitionRegistryInterface::class);
        $this->actionHelperResolver     = $this->createStub(ActionHelperResolver::class);
        $this->defaultActionHelper      = $this->createStub(CreateActionHelperInterface::class);
        $this->formCreator              = $this->createStub(FormCreator::class);
        $this->objectManagerResolver    = $this->createStub(EntityManagerResolver::class);
        $this->redirectionGenerator     = $this->createStub(RedirectionGenerator::class);
        $this->viewRenderer             = $this->createStub(ViewRenderer::class);
    }

    public function testInvokeRendersFormOnGet(): void
    {
        $actionDefinition = $this->givenActionDefinition(stdClass::class, 'create');

        $this->actionDefinitionRegistry
            ->method('get')
            ->willReturn($actionDefinition)
        ;

        $this->actionHelperResolver
            ->method('resolve')
            ->willReturn($this->defaultActionHelper)
        ;

        $this->defaultActionHelper
            ->method('createEntity')
            ->willReturn(new stdClass())
        ;

        $form = $this->createStub(FormInterface::class);
        $form->method('isSubmitted')->willReturn(false);
        $form->method('createView')->willReturn(new FormView());

        $this->formCreator
            ->method('create')
            ->willReturn($form)
        ;

        $response = new Response('form');

        $this->viewRenderer
            ->method('render')
            ->willReturn($response)
        ;

        $result = $this->createAction()->__invoke(
            Request::create(
                '/foo',
                Request::METHOD_GET,
            ),
            stdClass::class,
        );

        self::assertSame($response, $result);
    }

    public function testInvokeRedirectsOnValidPost(): void
    {
        $actionDefinition = $this->givenActionDefinition(stdClass::class, 'create');

        $this->actionDefinitionRegistry
            ->method('get')
            ->willReturn($actionDefinition)
        ;

        $this->actionHelperResolver
            ->method('resolve')
            ->willReturn($this->defaultActionHelper)
        ;

        $this->defaultActionHelper
            ->method('createEntity')
            ->willReturn(new stdClass())
        ;

        $form = $this->createStub(FormInterface::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);

        $this->formCreator
            ->method('create')
            ->willReturn($form)
        ;

        $redirectResponse = new RedirectResponse('/list');

        $this->redirectionGenerator
            ->method('generate')
            ->willReturn($redirectResponse)
        ;

        $result = $this->createAction()->__invoke(
            Request::create(
                '/foo',
                Request::METHOD_POST,
            ),
            stdClass::class,
        );

        self::assertSame($redirectResponse, $result);
    }

    /**
     * @return CreateAction<stdClass>
     */
    private function createAction(): CreateAction
    {
        return new CreateAction(
            $this->actionDefinitionRegistry,
            $this->actionHelperResolver,
            $this->defaultActionHelper,
            $this->formCreator,
            $this->objectManagerResolver,
            $this->redirectionGenerator,
            $this->viewRenderer,
        );
    }

    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     */
    private function givenActionDefinition(
        string $entityClass,
        string $action,
    ): ActionDefinition {
        return new ActionDefinition(
            entityAction:          new EntityAction(
                                       $entityClass,
                                       CrudAction::from($action),
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
                                       name:         'route.name',
                                       path:         '',
                                       requirements: [],
                                   ),
            viewDefinition:        new ViewDefinition(
                                       path:         '',
                                       variables:    [],
                                       fallbackMode: FallbackMode::PROVIDE,
                                   ),
        );
    }
}
