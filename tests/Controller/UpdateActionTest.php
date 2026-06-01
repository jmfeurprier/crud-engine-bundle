<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Controller;

use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Redirection\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\ActionRouteConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ViewFallbackMode;
use Jmf\CrudEngine\Controller\Dependencies\EntityFinder;
use Jmf\CrudEngine\Controller\Dependencies\EntityManagerResolver;
use Jmf\CrudEngine\Controller\Dependencies\FormCreator;
use Jmf\CrudEngine\Controller\Dependencies\RedirectionGenerator;
use Jmf\CrudEngine\Controller\Dependencies\ViewRenderer;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\UpdateActionHelperInterface;
use Jmf\CrudEngine\Controller\UpdateAction;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UpdateActionTest extends TestCase
{
    private ActionConfigurationRepositoryInterface&Stub $actionConfigurationRepository;

    private ActionHelperResolver&Stub $actionHelperResolver;

    /**
     * @var UpdateActionHelperInterface<stdClass>&Stub
     */
    private UpdateActionHelperInterface&Stub $defaultActionHelper;

    private EntityFinder&Stub $entityFinder;

    private FormCreator&Stub $formCreator;

    private EntityManagerResolver&Stub $entityManagerResolver;

    private RedirectionGenerator&Stub $redirectionGenerator;

    private ViewRenderer&Stub $viewRenderer;

    #[Override]
    protected function setUp(): void
    {
        $this->actionConfigurationRepository = $this->createStub(ActionConfigurationRepositoryInterface::class);
        $this->actionHelperResolver          = $this->createStub(ActionHelperResolver::class);
        $this->defaultActionHelper           = $this->createStub(UpdateActionHelperInterface::class);
        $this->entityFinder                  = $this->createStub(EntityFinder::class);
        $this->formCreator                   = $this->createStub(FormCreator::class);
        $this->entityManagerResolver         = $this->createStub(EntityManagerResolver::class);
        $this->redirectionGenerator          = $this->createStub(RedirectionGenerator::class);
        $this->viewRenderer                  = $this->createStub(ViewRenderer::class);
    }

    public function testInvokeRendersFormOnGet(): void
    {
        $actionConfiguration = $this->givenActionConfiguration(stdClass::class, 'update');

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
            '42',
        );

        self::assertSame($response, $result);
    }

    public function testInvokeRedirectsOnValidPost(): void
    {
        $actionConfiguration = $this->givenActionConfiguration(stdClass::class, 'update');

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
            '42',
        );

        self::assertSame($redirectResponse, $result);
    }

    /**
     * @return UpdateAction<stdClass>
     */
    private function createAction(): UpdateAction
    {
        return new UpdateAction(
            $this->actionConfigurationRepository,
            $this->actionHelperResolver,
            $this->defaultActionHelper,
            $this->entityFinder,
            $this->formCreator,
            $this->entityManagerResolver,
            $this->redirectionGenerator,
            $this->viewRenderer,
        );
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
            formTypeClass:            null,
            helperClass:              null,
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
                                          viewFallbackMode: ViewFallbackMode::RENDER_BUILT_IN,
                                      ),
        );
    }
}
