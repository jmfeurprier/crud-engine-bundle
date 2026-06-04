<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Routing;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Form\ActionFormConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Form\FormFallbackMode;
use Jmf\CrudEngine\Configuration\Entities\Action\Redirection\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\ActionRouteConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ViewFallbackMode;
use Jmf\CrudEngine\Configuration\Repository\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Exception\CrudEngineUnsupportedActionException;
use Jmf\CrudEngine\Routing\IndexActionRouteLoader;
use Jmf\CrudEngine\Routing\RouteLoader;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Routing\RouteCollection;

final class RouteLoaderTest extends TestCase
{
    private ActionConfigurationRepositoryInterface&Stub $actionConfigurationRepository;

    #[Override]
    protected function setUp(): void
    {
        $this->actionConfigurationRepository = $this->createStub(ActionConfigurationRepositoryInterface::class);
    }

    public function testInvokeReturnsRouteCollection(): void
    {
        $actionConfiguration = $this->givenActionConfiguration(
            entityClass: stdClass::class,
            action:      'index',
            routeName:   'foo.index',
            routePath:   'foo/bar',
        );

        $this->actionConfigurationRepository
            ->method('all')
            ->willReturn([$actionConfiguration])
        ;

        $routeLoader = new RouteLoader(
            $this->actionConfigurationRepository,
            [new IndexActionRouteLoader()],
        );

        $routeCollection = $routeLoader->__invoke();

        self::assertInstanceOf(RouteCollection::class, $routeCollection);
        self::assertCount(1, $routeCollection->all());
        self::assertNotNull($routeCollection->get('foo.index'));
    }

    public function testInvokeReturnsEmptyCollectionWhenNoConfigurations(): void
    {
        $this->actionConfigurationRepository
            ->method('all')
            ->willReturn([])
        ;

        $routeLoader = new RouteLoader(
            $this->actionConfigurationRepository,
            [new IndexActionRouteLoader()],
        );

        $routeCollection = $routeLoader->__invoke();

        self::assertInstanceOf(RouteCollection::class, $routeCollection);
        self::assertCount(0, $routeCollection->all());
    }

    public function testInvokeThrowsOnUnsupportedAction(): void
    {
        $actionConfiguration = $this->givenActionConfiguration(
            entityClass: stdClass::class,
            action:      'unsupported',
            routeName:   'foo.unsupported',
            routePath:   'foo/bar',
        );

        $this->actionConfigurationRepository
            ->method('all')
            ->willReturn([$actionConfiguration])
        ;

        $routeLoader = new RouteLoader(
            $this->actionConfigurationRepository,
            [new IndexActionRouteLoader()],
        );

        $this->expectException(CrudEngineUnsupportedActionException::class);

        $routeLoader->__invoke();
    }

    /**
     * @param class-string $entityClass
     */
    private function givenActionConfiguration(
        string $entityClass,
        string $action,
        string $routeName,
        string $redirectionRoute = '',
        string $routePath = '',
        string $viewPath = '',
    ): ActionConfiguration {
        $actionRedirectionConfiguration = new ActionRedirectionConfiguration(
            route:      $redirectionRoute,
            parameters: [],
        );

        $actionRouteConfiguration = new ActionRouteConfiguration(
            name:         $routeName,
            path:         $routePath,
            requirements: [],
        );

        $actionViewConfiguration = new ActionViewConfiguration(
            path:             $viewPath,
            variables:        [],
            viewFallbackMode: ViewFallbackMode::PROVIDE,
        );

        return new ActionConfiguration(
            entityClass:              $entityClass,
            action:                   $action,
            helperClass:              null,
            formConfiguration:        new ActionFormConfiguration(
                                          formTypeClass:    null,
                                          suggestedFormTypeClass: 'StubFormType',
                                          formFallbackMode: FormFallbackMode::PROVIDE,
                                      ),
            redirectionConfiguration: $actionRedirectionConfiguration,
            routeConfiguration:       $actionRouteConfiguration,
            viewConfiguration:        $actionViewConfiguration,
        );
    }
}
