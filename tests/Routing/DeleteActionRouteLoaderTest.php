<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Routing;

use Jmf\CrudEngine\Controller\DeleteAction;
use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Definition\FallbackMode;
use Jmf\CrudEngine\Definition\FormDefinition;
use Jmf\CrudEngine\Definition\RedirectionDefinition;
use Jmf\CrudEngine\Definition\RouteDefinition;
use Jmf\CrudEngine\Definition\ViewDefinition;
use Jmf\CrudEngine\Model\CrudAction;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Routing\DeleteActionRouteLoader;
use Override;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class DeleteActionRouteLoaderTest extends TestCase
{
    private DeleteActionRouteLoader $deleteActionRouteLoader;

    private RouteCollection $routeCollection;

    #[Override]
    protected function setUp(): void
    {
        $this->deleteActionRouteLoader = new DeleteActionRouteLoader();

        $this->routeCollection = new RouteCollection();
    }

    public function testLoad(): void
    {
        $actionDefinition = $this->givenActionDefinition(
            entityClass: stdClass::class,
            action:      'delete',
            routeName:   'foo.delete',
            routePath:   'foo/bar/{id}/delete',
        );

        $this->deleteActionRouteLoader->load($this->routeCollection, $actionDefinition);

        self::assertCount(1, $this->routeCollection->all());

        $route = $this->routeCollection->get('foo.delete');

        self::assertInstanceOf(Route::class, $route);
        self::assertSame('/foo/bar/{id}/delete', $route->getPath());
        self::assertSame(
            [
                'GET',
                'POST',
            ],
            $route->getMethods(),
        );
        self::assertSame(DeleteAction::class, $route->getDefault('_controller'));
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
