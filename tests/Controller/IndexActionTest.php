<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Controller;

use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\IndexActionHelperInterface;
use Jmf\CrudEngine\Controller\IndexAction;
use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Definition\FallbackMode;
use Jmf\CrudEngine\Definition\FormDefinition;
use Jmf\CrudEngine\Definition\RedirectionDefinition;
use Jmf\CrudEngine\Definition\RouteDefinition;
use Jmf\CrudEngine\Definition\ViewDefinition;
use Jmf\CrudEngine\Model\CrudAction;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Persistence\EntityManagerResolver;
use Jmf\CrudEngine\Registry\ActionDefinitionRegistryInterface;
use Jmf\CrudEngine\View\ViewRenderer;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class IndexActionTest extends TestCase
{
    private ActionDefinitionRegistryInterface & Stub $actionDefinitionRegistry;

    private ActionHelperResolver & Stub $actionHelperResolver;

    /**
     * @var IndexActionHelperInterface<stdClass> & Stub
     */
    private IndexActionHelperInterface & Stub $defaultActionHelper;

    private EntityManagerResolver & Stub $objectManagerResolver;

    private ViewRenderer & Stub $viewRenderer;

    #[Override]
    protected function setUp(): void
    {
        $this->actionDefinitionRegistry = $this->createStub(ActionDefinitionRegistryInterface::class);
        $this->actionHelperResolver     = $this->createStub(ActionHelperResolver::class);
        $this->defaultActionHelper      = $this->createStub(IndexActionHelperInterface::class);
        $this->objectManagerResolver    = $this->createStub(EntityManagerResolver::class);
        $this->viewRenderer             = $this->createStub(ViewRenderer::class);
    }

    public function testInvokeRendersView(): void
    {
        $actionDefinition = $this->givenActionDefinition(stdClass::class, 'index');

        $this->actionDefinitionRegistry
            ->method('get')
            ->willReturn($actionDefinition)
        ;

        $this->actionHelperResolver
            ->method('resolve')
            ->willReturn($this->defaultActionHelper)
        ;

        $this->defaultActionHelper
            ->method('getEntities')
            ->willReturn([new stdClass()])
        ;

        $expectedResponse = new Response('rendered');

        $this->viewRenderer
            ->method('render')
            ->willReturn($expectedResponse)
        ;

        $result = $this->createAction()->__invoke(
            new Request(),
            stdClass::class,
        );

        self::assertSame($expectedResponse, $result);
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
                                       name:         '',
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

    /**
     * @return IndexAction<stdClass>
     */
    private function createAction(): IndexAction
    {
        return new IndexAction(
            $this->actionDefinitionRegistry,
            $this->actionHelperResolver,
            $this->defaultActionHelper,
            $this->objectManagerResolver,
            $this->viewRenderer,
        );
    }
}
