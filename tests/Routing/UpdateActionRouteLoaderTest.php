<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Routing;

use Jmf\CrudEngine\Controller\UpdateAction;
use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Definition\FallbackMode;
use Jmf\CrudEngine\Definition\FormDefinition;
use Jmf\CrudEngine\Definition\RedirectionDefinition;
use Jmf\CrudEngine\Definition\RouteDefinition;
use Jmf\CrudEngine\Definition\ViewDefinition;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Routing\UpdateActionRouteLoader;
use Override;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class UpdateActionRouteLoaderTest extends TestCase
{
    private UpdateActionRouteLoader $updateActionRouteLoader;

    private RouteCollection $routeCollection;

    #[Override]
    protected function setUp(): void
    {
        $this->updateActionRouteLoader = new UpdateActionRouteLoader();

        $this->routeCollection = new RouteCollection();
    }

    public function testLoad(): void
    {
        $actionDefinition = $this->givenActionDefinition(
            entityClass: stdClass::class,
            action:      'update',
            routeName:   'foo.update',
            routePath:   'foo/bar/{id}/edit',
        );

        $this->updateActionRouteLoader->load($this->routeCollection, $actionDefinition);

        self::assertCount(1, $this->routeCollection->all());

        $route = $this->routeCollection->get('foo.update');

        self::assertInstanceOf(Route::class, $route);
        self::assertSame('/foo/bar/{id}/edit', $route->getPath());
        self::assertSame(
            [
                'GET',
                'POST',
            ],
            $route->getMethods(),
        );
        self::assertSame(UpdateAction::class, $route->getDefault('_controller'));
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
            viewFallbackMode: FallbackMode::PROVIDE,
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
                                          formFallbackMode:       FallbackMode::PROVIDE,
                                      ),
            redirectionConfiguration: $actionRedirectionConfiguration,
            routeConfiguration:       $actionRouteConfiguration,
            viewConfiguration:        $actionViewConfiguration,
        );
    }
}
