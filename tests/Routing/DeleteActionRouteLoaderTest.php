<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Routing;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Redirection\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\ActionRouteConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ViewFallbackMode;
use Jmf\CrudEngine\Controller\DeleteAction;
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
        $actionConfiguration = $this->givenActionConfiguration(
            entityClass: stdClass::class,
            action:      'delete',
            routeName:   'foo.delete',
            routePath:   'foo/bar/{id}/delete',
        );

        $this->deleteActionRouteLoader->load($this->routeCollection, $actionConfiguration);

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
            viewFallbackMode: ViewFallbackMode::RENDER_BUILT_IN,
        );

        return new ActionConfiguration(
            entityClass:              $entityClass,
            action:                   $action,
            formTypeClass:            null,
            helperClass:              null,
            redirectionConfiguration: $actionRedirectionConfiguration,
            routeConfiguration:       $actionRouteConfiguration,
            viewConfiguration:        $actionViewConfiguration,
        );
    }
}
