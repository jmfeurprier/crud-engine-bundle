<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Routing;

use Jmf\CrudEngine\Configuration\Action\Form\ActionFormConfiguration;
use Jmf\CrudEngine\Configuration\Action\Form\FormFallbackMode;
use Jmf\CrudEngine\Configuration\Action\Redirection\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Configuration\Action\Route\ActionRouteConfiguration;
use Jmf\CrudEngine\Configuration\Action\View\ActionViewConfiguration;
use Jmf\CrudEngine\Configuration\Action\View\ViewFallbackMode;
use Jmf\CrudEngine\Exception\CrudEngineUnsupportedActionException;
use Jmf\CrudEngine\Model\ActionConfiguration;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Registry\ActionConfigurationRegistryInterface;
use Jmf\CrudEngine\Routing\IndexActionRouteLoader;
use Jmf\CrudEngine\Routing\RouteLoader;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Routing\RouteCollection;

final class RouteLoaderTest extends TestCase
{
    private ActionConfigurationRegistryInterface&Stub $actionConfigurationRegistry;

    #[Override]
    protected function setUp(): void
    {
        $this->actionConfigurationRegistry = $this->createStub(ActionConfigurationRegistryInterface::class);
    }

    public function testInvokeReturnsRouteCollection(): void
    {
        $actionConfiguration = $this->givenActionConfiguration(
            entityClass: stdClass::class,
            action:      'index',
            routeName:   'foo.index',
            routePath:   'foo/bar',
        );

        $this->actionConfigurationRegistry
            ->method('all')
            ->willReturn([$actionConfiguration])
        ;

        $routeLoader = new RouteLoader(
            $this->actionConfigurationRegistry,
            [new IndexActionRouteLoader()],
        );

        $routeCollection = $routeLoader->__invoke();

        self::assertInstanceOf(RouteCollection::class, $routeCollection);
        self::assertCount(1, $routeCollection->all());
        self::assertNotNull($routeCollection->get('foo.index'));
    }

    public function testInvokeReturnsEmptyCollectionWhenNoConfigurations(): void
    {
        $this->actionConfigurationRegistry
            ->method('all')
            ->willReturn([])
        ;

        $routeLoader = new RouteLoader(
            $this->actionConfigurationRegistry,
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

        $this->actionConfigurationRegistry
            ->method('all')
            ->willReturn([$actionConfiguration])
        ;

        $routeLoader = new RouteLoader(
            $this->actionConfigurationRegistry,
            [new IndexActionRouteLoader()],
        );

        $this->expectException(CrudEngineUnsupportedActionException::class);

        $routeLoader->__invoke();
    }

    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
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
            redirectionConfiguration: $actionRedirectionConfiguration,
            routeConfiguration:       $actionRouteConfiguration,
            viewConfiguration:        $actionViewConfiguration,
        );
    }
}
