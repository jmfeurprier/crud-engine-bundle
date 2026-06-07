<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Routing;

use Jmf\CrudEngine\Exception\CrudEngineUnsupportedActionException;
use Jmf\CrudEngine\Form\FormFallbackMode;
use Jmf\CrudEngine\Model\ActionDefinition;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Registry\ActionDefinitionRegistryInterface;
use Jmf\CrudEngine\Registry\FormDefinition;
use Jmf\CrudEngine\Registry\RedirectionDefinition;
use Jmf\CrudEngine\Registry\RouteDefinition;
use Jmf\CrudEngine\Registry\ViewDefinition;
use Jmf\CrudEngine\Routing\IndexActionRouteLoader;
use Jmf\CrudEngine\Routing\RouteLoader;
use Jmf\CrudEngine\View\ViewFallbackMode;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Routing\RouteCollection;

final class RouteLoaderTest extends TestCase
{
    private ActionDefinitionRegistryInterface&Stub $actionDefinitionRegistry;

    #[Override]
    protected function setUp(): void
    {
        $this->actionDefinitionRegistry = $this->createStub(ActionDefinitionRegistryInterface::class);
    }

    public function testInvokeReturnsRouteCollection(): void
    {
        $actionDefinition = $this->givenActionDefinition(
            entityClass: stdClass::class,
            action:      'index',
            routeName:   'foo.index',
            routePath:   'foo/bar',
        );

        $this->actionDefinitionRegistry
            ->method('all')
            ->willReturn([$actionDefinition])
        ;

        $routeLoader = new RouteLoader(
            $this->actionDefinitionRegistry,
            [new IndexActionRouteLoader()],
        );

        $routeCollection = $routeLoader->__invoke();

        self::assertInstanceOf(RouteCollection::class, $routeCollection);
        self::assertCount(1, $routeCollection->all());
        self::assertNotNull($routeCollection->get('foo.index'));
    }

    public function testInvokeReturnsEmptyCollectionWhenNoConfigurations(): void
    {
        $this->actionDefinitionRegistry
            ->method('all')
            ->willReturn([])
        ;

        $routeLoader = new RouteLoader(
            $this->actionDefinitionRegistry,
            [new IndexActionRouteLoader()],
        );

        $routeCollection = $routeLoader->__invoke();

        self::assertInstanceOf(RouteCollection::class, $routeCollection);
        self::assertCount(0, $routeCollection->all());
    }

    public function testInvokeThrowsOnUnsupportedAction(): void
    {
        $actionDefinition = $this->givenActionDefinition(
            entityClass: stdClass::class,
            action:      'unsupported',
            routeName:   'foo.unsupported',
            routePath:   'foo/bar',
        );

        $this->actionDefinitionRegistry
            ->method('all')
            ->willReturn([$actionDefinition])
        ;

        $routeLoader = new RouteLoader(
            $this->actionDefinitionRegistry,
            [new IndexActionRouteLoader()],
        );

        $this->expectException(CrudEngineUnsupportedActionException::class);

        $routeLoader->__invoke();
    }

    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     */
    private function givenActionDefinition(
        string $entityClass,
        string $action,
        string $routeName,
        string $redirectionRoute = '',
        string $routePath = '',
        string $viewPath = '',
    ): ActionDefinition {
        $actionRedirectionConfiguration = new RedirectionDefinition(
            route:      $redirectionRoute,
            parameters: [],
        );

        $actionRouteConfiguration = new RouteDefinition(
            name:         $routeName,
            path:         $routePath,
            requirements: [],
        );

        $actionViewConfiguration = new ViewDefinition(
            path:             $viewPath,
            variables:        [],
            viewFallbackMode: ViewFallbackMode::PROVIDE,
        );

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
            redirectionConfiguration: $actionRedirectionConfiguration,
            routeConfiguration:       $actionRouteConfiguration,
            viewConfiguration:        $actionViewConfiguration,
        );
    }
}
