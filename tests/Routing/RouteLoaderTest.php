<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Routing;

use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Definition\FallbackMode;
use Jmf\CrudEngine\Definition\FormDefinition;
use Jmf\CrudEngine\Definition\RedirectionDefinition;
use Jmf\CrudEngine\Definition\RouteDefinition;
use Jmf\CrudEngine\Definition\ViewDefinition;
use Jmf\CrudEngine\Exception\CrudEngineUnsupportedActionException;
use Jmf\CrudEngine\Model\CrudAction;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Registry\ActionDefinitionRegistryInterface;
use Jmf\CrudEngine\Routing\IndexActionRouteLoader;
use Jmf\CrudEngine\Routing\RouteLoader;
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

    public function testInvokeReturnsEmptyCollectionWhenNoDefinitions(): void
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
        $redirectionDefinition = new RedirectionDefinition(
            route:      $redirectionRoute,
            parameters: [],
        );

        $routeDefinition = new RouteDefinition(
            name:         $routeName,
            path:         $routePath,
            requirements: [],
        );

        $viewDefinition = new ViewDefinition(
            path:         $viewPath,
            variables:    [],
            fallbackMode: FallbackMode::PROVIDE,
        );

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
            redirectionDefinition: $redirectionDefinition,
            routeDefinition:       $routeDefinition,
            viewDefinition:        $viewDefinition,
        );
    }
}
