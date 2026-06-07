<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Routing;

use Jmf\CrudEngine\Form\FormFallbackMode;
use Jmf\CrudEngine\Model\ActionConfiguration;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Registry\ActionFormConfiguration;
use Jmf\CrudEngine\Registry\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Registry\ActionRouteConfiguration;
use Jmf\CrudEngine\Registry\ActionViewConfiguration;
use Jmf\CrudEngine\Routing\IndexActionRouteLoader;
use Jmf\CrudEngine\View\ViewFallbackMode;
use Override;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class IndexActionRouteLoaderTest extends TestCase
{
    private IndexActionRouteLoader $indexActionRouteLoader;

    private RouteCollection $routeCollection;

    #[Override]
    protected function setUp(): void
    {
        $this->indexActionRouteLoader = new IndexActionRouteLoader();

        $this->routeCollection = new RouteCollection();
    }

    public function testLoad(): void
    {
        $actionConfiguration = $this->givenActionConfiguration(
            entityClass: stdClass::class,
            action:      'index',
            routeName:   'foo.index',
            routePath:   'foo/bar',
        );

        $this->indexActionRouteLoader->load($this->routeCollection, $actionConfiguration);

        self::assertCount(1, $this->routeCollection->all());

        $route = $this->routeCollection->get('foo.index');

        self::assertInstanceOf(Route::class, $route);
        self::assertSame('/foo/bar', $route->getPath());
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
