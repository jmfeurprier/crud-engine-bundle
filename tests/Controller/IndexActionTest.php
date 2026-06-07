<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Controller;

use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\IndexActionHelperInterface;
use Jmf\CrudEngine\Controller\IndexAction;
use Jmf\CrudEngine\Form\FormFallbackMode;
use Jmf\CrudEngine\Model\ActionConfiguration;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Persistence\EntityManagerResolver;
use Jmf\CrudEngine\Registry\ActionConfigurationRegistryInterface;
use Jmf\CrudEngine\Registry\ActionFormConfiguration;
use Jmf\CrudEngine\Registry\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Registry\ActionRouteConfiguration;
use Jmf\CrudEngine\Registry\ActionViewConfiguration;
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
    private ActionConfigurationRegistryInterface & Stub $actionConfigurationRegistry;

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
        $this->actionConfigurationRegistry = $this->createStub(ActionConfigurationRegistryInterface::class);
        $this->actionHelperResolver          = $this->createStub(ActionHelperResolver::class);
        $this->defaultActionHelper           = $this->createStub(IndexActionHelperInterface::class);
        $this->objectManagerResolver         = $this->createStub(EntityManagerResolver::class);
        $this->viewRenderer                  = $this->createStub(ViewRenderer::class);
    }

    public function testInvokeRendersView(): void
    {
        $actionConfiguration = $this->givenActionConfiguration(stdClass::class, 'index');

        $this->actionConfigurationRegistry
            ->method('get')
            ->willReturn($actionConfiguration)
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
    private function givenActionConfiguration(
        string $entityClass,
        string $action,
    ): ActionConfiguration {
        return new ActionConfiguration(
            entityAction:             new EntityAction(
                                          $entityClass,
                                          $action,
                                      ),
            helperClass:              null,
            formConfiguration:        new ActionFormConfiguration(
                                          formTypeClass:          null,
                                          suggestedFormTypeClass: 'StubFormType',
                                          formFallbackMode:       FormFallbackMode::PROVIDE,
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
            $this->actionConfigurationRegistry,
            $this->actionHelperResolver,
            $this->defaultActionHelper,
            $this->objectManagerResolver,
            $this->viewRenderer,
        );
    }
}
