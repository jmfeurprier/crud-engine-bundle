<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Routing;

use Jmf\CrudEngine\Controller\ReadAction;
use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Definition\FallbackMode;
use Jmf\CrudEngine\Definition\FormDefinition;
use Jmf\CrudEngine\Definition\RedirectionDefinition;
use Jmf\CrudEngine\Definition\RouteDefinition;
use Jmf\CrudEngine\Definition\ViewDefinition;
use Jmf\CrudEngine\Model\CrudAction;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Routing\ReadActionRouteLoader;
use Override;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class ReadActionRouteLoaderTest extends TestCase
{
    private ReadActionRouteLoader $readActionRouteLoader;

    private RouteCollection $routeCollection;

    #[Override]
    protected function setUp(): void
    {
        $this->readActionRouteLoader = new ReadActionRouteLoader();

        $this->routeCollection = new RouteCollection();
    }

    public function testLoad(): void
    {
        $actionDefinition = $this->givenActionDefinition(
            entityClass: stdClass::class,
            action:      'read',
            routeName:   'foo.read',
            routePath:   'foo/bar/{id}',
        );

        $this->readActionRouteLoader->load($this->routeCollection, $actionDefinition);

        self::assertCount(1, $this->routeCollection->all());

        $route = $this->routeCollection->get('foo.read');

        self::assertInstanceOf(Route::class, $route);
        self::assertSame('/foo/bar/{id}', $route->getPath());
        self::assertSame(['GET'], $route->getMethods());
        self::assertSame(ReadAction::class, $route->getDefault('_controller'));
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
