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
use Jmf\CrudEngine\Controller\CreateAction;
use Jmf\CrudEngine\Routing\CreateActionRouteLoader;
use Override;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class CreateActionRouteLoaderTest extends TestCase
{
    private CreateActionRouteLoader $createActionRouteLoader;

    private RouteCollection $routeCollection;

    #[Override]
    protected function setUp(): void
    {
        $this->createActionRouteLoader = new CreateActionRouteLoader();

        $this->routeCollection = new RouteCollection();
    }

    public function testLoad(): void
    {
        $actionConfiguration = $this->givenActionConfiguration(
            entityClass: stdClass::class,
            action:      'create',
            routeName:   'foo.create',
            routePath:   'foo/bar/create',
        );

        $this->createActionRouteLoader->load($this->routeCollection, $actionConfiguration);

        self::assertCount(1, $this->routeCollection->all());

        $route = $this->routeCollection->get('foo.create');

        self::assertInstanceOf(Route::class, $route);
        self::assertSame('/foo/bar/create', $route->getPath());
        self::assertSame(
            [
                'GET',
                'POST',
            ],
            $route->getMethods(),
        );
        self::assertSame(CreateAction::class, $route->getDefault('_controller'));
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
            viewFallbackMode: ViewFallbackMode::BUILT_IN,
        );

        return new ActionConfiguration(
            entityClass:              $entityClass,
            action:                   $action,
            helperClass:              null,
            formConfiguration:        new ActionFormConfiguration(
                                          formTypeClass:    null,
                                          formFallbackMode: FormFallbackMode::BUILT_IN,
                                      ),
            redirectionConfiguration: $actionRedirectionConfiguration,
            routeConfiguration:       $actionRouteConfiguration,
            viewConfiguration:        $actionViewConfiguration,
        );
    }
}
