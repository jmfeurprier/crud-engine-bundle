<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Routing;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Redirection\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Redirection\ActionRedirectionParameterCollection;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\Requirements\ActionRouteRequirementCollection;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\ActionRouteConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\Variables\ActionViewVariablesCollection;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Routing\IndexActionRouteLoader;
use Override;
use PHPUnit\Framework\TestCase;
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

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    public function testLoad(): void
    {
        $actionConfiguration = $this->givenActionConfiguration(
            entityClass: \stdClass::class,
            action:      'index',
            routeName:   'foo.index',
            routePath:   'foo/bar',
        );

        $this->indexActionRouteLoader->load($this->routeCollection, $actionConfiguration);

        $this->assertCount(1, $this->routeCollection->all());

        $route = $this->routeCollection->get('foo.index');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('/foo/bar', $route->getPath());
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
            parameters: ActionRedirectionParameterCollection::createDefault(),
        );

        $actionRouteConfiguration = new ActionRouteConfiguration(
            name:         $routeName,
            path:         $routePath,
            requirements: ActionRouteRequirementCollection::createDefault(),
        );

        $actionViewConfiguration = new ActionViewConfiguration(
            path:      $viewPath,
            variables: new ActionViewVariablesCollection([]),
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
