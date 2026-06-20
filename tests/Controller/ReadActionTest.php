<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Controller;

use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\ReadActionHelperInterface;
use Jmf\CrudEngine\Controller\ReadAction;
use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Definition\FallbackMode;
use Jmf\CrudEngine\Definition\FormDefinition;
use Jmf\CrudEngine\Definition\RedirectionDefinition;
use Jmf\CrudEngine\Definition\RouteDefinition;
use Jmf\CrudEngine\Definition\ViewDefinition;
use Jmf\CrudEngine\Model\CrudAction;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Persistence\EntityFinder;
use Jmf\CrudEngine\Registry\ActionDefinitionRegistryInterface;
use Jmf\CrudEngine\View\ViewRenderer;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ReadActionTest extends TestCase
{
    private ActionDefinitionRegistryInterface&Stub $actionDefinitionRegistry;

    private ActionHelperResolver&Stub $actionHelperResolver;

    /**
     * @var ReadActionHelperInterface<stdClass>&Stub
     */
    private ReadActionHelperInterface&Stub $defaultActionHelper;

    private EntityFinder&Stub $entityFinder;

    private ViewRenderer&Stub $viewRenderer;

    #[Override]
    protected function setUp(): void
    {
        $this->actionDefinitionRegistry = $this->createStub(ActionDefinitionRegistryInterface::class);
        $this->actionHelperResolver          = $this->createStub(ActionHelperResolver::class);
        $this->defaultActionHelper           = $this->createStub(ReadActionHelperInterface::class);
        $this->entityFinder                  = $this->createStub(EntityFinder::class);
        $this->viewRenderer                  = $this->createStub(ViewRenderer::class);
    }

    public function testInvokeRendersView(): void
    {
        $actionDefinition = $this->givenActionDefinition(stdClass::class, 'read');

        $this->actionDefinitionRegistry
            ->method('get')
            ->willReturn($actionDefinition)
        ;

        $this->actionHelperResolver
            ->method('resolve')
            ->willReturn($this->defaultActionHelper)
        ;

        $entity = new stdClass();

        $this->entityFinder
            ->method('find')
            ->willReturn($entity)
        ;

        $expectedResponse = new Response('rendered');

        $this->viewRenderer
            ->method('render')
            ->willReturn($expectedResponse)
        ;

        $result = $this->createAction()->__invoke(
            new Request(),
            '42',
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
            entityAction:             new EntityAction(
                                          $entityClass,
                                          CrudAction::from($action),
                                      ),
            helperClass:              null,
            formDefinition:        new FormDefinition(
                                          formTypeClass:          null,
                                          suggestedFormTypeClass: 'StubFormType',
                                          fallbackMode:       FallbackMode::PROVIDE,
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
                                          path:             '',
                                          variables:        [],
                                          fallbackMode: FallbackMode::PROVIDE,
                                      ),
        );
    }

    /**
     * @return ReadAction<stdClass>
     */
    private function createAction(): ReadAction
    {
        return new ReadAction(
            $this->actionDefinitionRegistry,
            $this->actionHelperResolver,
            $this->defaultActionHelper,
            $this->entityFinder,
            $this->viewRenderer,
        );
    }
}
