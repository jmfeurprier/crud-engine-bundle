<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Controller;

use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Form\ActionFormConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Form\FormFallbackMode;
use Jmf\CrudEngine\Configuration\Entities\Action\Redirection\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\ActionRouteConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ViewFallbackMode;
use Jmf\CrudEngine\Controller\Dependencies\EntityFinder;
use Jmf\CrudEngine\Controller\Dependencies\ViewRenderer;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\ReadActionHelperInterface;
use Jmf\CrudEngine\Controller\ReadAction;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ReadActionTest extends TestCase
{
    private ActionConfigurationRepositoryInterface&Stub $actionConfigurationRepository;

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
        $this->actionConfigurationRepository = $this->createStub(ActionConfigurationRepositoryInterface::class);
        $this->actionHelperResolver          = $this->createStub(ActionHelperResolver::class);
        $this->defaultActionHelper           = $this->createStub(ReadActionHelperInterface::class);
        $this->entityFinder                  = $this->createStub(EntityFinder::class);
        $this->viewRenderer                  = $this->createStub(ViewRenderer::class);
    }

    public function testInvokeRendersView(): void
    {
        $actionConfiguration = $this->givenActionConfiguration(stdClass::class, 'read');

        $this->actionConfigurationRepository
            ->method('get')
            ->willReturn($actionConfiguration)
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
     * @param class-string $entityClass
     */
    private function givenActionConfiguration(
        string $entityClass,
        string $action,
    ): ActionConfiguration {
        return new ActionConfiguration(
            entityClass:              $entityClass,
            action:                   $action,
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
                                          path:             '',
                                          variables:        [],
                                          viewFallbackMode: ViewFallbackMode::PROVIDE,
                                      ),
        );
    }

    /**
     * @return ReadAction<stdClass>
     */
    private function createAction(): ReadAction
    {
        return new ReadAction(
            $this->actionConfigurationRepository,
            $this->actionHelperResolver,
            $this->defaultActionHelper,
            $this->entityFinder,
            $this->viewRenderer,
        );
    }
}
