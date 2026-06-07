<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Controller;

use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\IndexActionHelperInterface;
use Jmf\CrudEngine\Controller\IndexAction;
use Jmf\CrudEngine\Form\FormFallbackMode;
use Jmf\CrudEngine\Model\ActionDefinition;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Persistence\EntityManagerResolver;
use Jmf\CrudEngine\Registry\ActionDefinitionRegistryInterface;
use Jmf\CrudEngine\Registry\FormDefinition;
use Jmf\CrudEngine\Registry\RedirectionDefinition;
use Jmf\CrudEngine\Registry\RouteDefinition;
use Jmf\CrudEngine\Registry\ViewDefinition;
use Jmf\CrudEngine\View\ViewFallbackMode;
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
        $this->actionHelperResolver          = $this->createStub(ActionHelperResolver::class);
        $this->defaultActionHelper           = $this->createStub(IndexActionHelperInterface::class);
        $this->objectManagerResolver         = $this->createStub(EntityManagerResolver::class);
        $this->viewRenderer                  = $this->createStub(ViewRenderer::class);
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
            entityAction:             new EntityAction(
                                          $entityClass,
                                          $action,
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
                                          path:             '',
                                          variables:        [],
                                          viewFallbackMode: ViewFallbackMode::PROVIDE,
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
