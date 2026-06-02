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
use Jmf\CrudEngine\Controller\DeleteAction;
use Jmf\CrudEngine\Controller\Dependencies\EntityFinder;
use Jmf\CrudEngine\Controller\Dependencies\EntityManagerResolver;
use Jmf\CrudEngine\Controller\Dependencies\RedirectionGenerator;
use Jmf\CrudEngine\Controller\Dependencies\ViewRenderer;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\DeleteActionHelperInterface;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DeleteActionTest extends TestCase
{
    private ActionConfigurationRepositoryInterface&Stub $actionConfigurationRepository;

    private ActionHelperResolver&Stub $actionHelperResolver;

    /**
     * @var DeleteActionHelperInterface<stdClass>&Stub
     */
    private DeleteActionHelperInterface&Stub $defaultActionHelper;

    private EntityFinder&Stub $entityFinder;

    private EntityManagerResolver&Stub $entityManagerResolver;

    private RedirectionGenerator&Stub $redirectionGenerator;

    private ViewRenderer&Stub $viewRenderer;

    #[Override]
    protected function setUp(): void
    {
        $this->actionConfigurationRepository = $this->createStub(ActionConfigurationRepositoryInterface::class);
        $this->actionHelperResolver          = $this->createStub(ActionHelperResolver::class);
        $this->defaultActionHelper           = $this->createStub(DeleteActionHelperInterface::class);
        $this->entityFinder                  = $this->createStub(EntityFinder::class);
        $this->entityManagerResolver         = $this->createStub(EntityManagerResolver::class);
        $this->redirectionGenerator          = $this->createStub(RedirectionGenerator::class);
        $this->viewRenderer                  = $this->createStub(ViewRenderer::class);
    }

    public function testInvokeRendersViewOnGet(): void
    {
        $actionConfiguration = $this->givenActionConfiguration(stdClass::class, 'delete');

        $this->actionConfigurationRepository
            ->method('get')
            ->willReturn($actionConfiguration)
        ;

        $this->actionHelperResolver
            ->method('resolve')
            ->willReturn($this->defaultActionHelper)
        ;

        $this->entityFinder
            ->method('find')
            ->willReturn(new stdClass())
        ;

        $response = new Response('confirm');

        $this->viewRenderer
            ->method('render')
            ->willReturn($response)
        ;

        $result = $this->createAction()->__invoke(
            Request::create('/foo', Request::METHOD_GET),
            stdClass::class,
            '42',
        );

        self::assertSame($response, $result);
    }

    public function testInvokeRedirectsOnPost(): void
    {
        $actionConfiguration = $this->givenActionConfiguration(stdClass::class, 'delete');

        $this->actionConfigurationRepository
            ->method('get')
            ->willReturn($actionConfiguration)
        ;

        $this->actionHelperResolver
            ->method('resolve')
            ->willReturn($this->defaultActionHelper)
        ;

        $this->entityFinder
            ->method('find')
            ->willReturn(new stdClass())
        ;

        $redirectResponse = new RedirectResponse('/list');

        $this->redirectionGenerator
            ->method('generate')
            ->willReturn($redirectResponse)
        ;

        $result = $this->createAction()->__invoke(
            Request::create('/foo', Request::METHOD_POST),
            stdClass::class,
            '42',
        );

        self::assertSame($redirectResponse, $result);
    }

    public function testInvokeCallsOnFailureWhenExceptionThrown(): void
    {
        $actionConfiguration = $this->givenActionConfiguration(stdClass::class, 'delete');

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

        $this->defaultActionHelper
            ->method('hookBeforeRemove')
            ->willThrowException(new RuntimeException('removal failed'))
        ;

        $response = new Response('failure');

        $this->defaultActionHelper
            ->method('onFailure')
            ->willReturn($response)
        ;

        $result = $this->createAction()->__invoke(
            Request::create('/foo', Request::METHOD_POST),
            stdClass::class,
            '42',
        );

        self::assertSame($response, $result);
    }

    /**
     * @return DeleteAction<stdClass>
     */
    private function createAction(): DeleteAction
    {
        return new DeleteAction(
            $this->actionConfigurationRepository,
            $this->actionHelperResolver,
            $this->defaultActionHelper,
            $this->entityFinder,
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
            helperClass:              null,
            formConfiguration:        new ActionFormConfiguration(
                                          formTypeClass:    null,
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
}
