<?php

namespace Jmf\CrudEngine\Tests\Routing;

use Jmf\CrudEngine\Configuration\Entities\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Redirection\ActionRedirectionConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\Requirements\ActionRouteRequirementCollection;
use Jmf\CrudEngine\Configuration\Entities\Action\Route\RouteConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfiguration;
use Jmf\CrudEngine\Configuration\Entities\Action\View\Variables\ActionViewVariablesCollection;
use Jmf\CrudEngine\Configuration\KeyStringCollection;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Routing\IndexActionRouteLoader;
use Override;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouteCollection;

class IndexActionRouteLoaderTest extends TestCase
{
    private IndexActionRouteLoader $loader;

    private RouteCollection $routeCollection;

    #[Override]
    protected function setUp(): void
    {
        $this->loader = new IndexActionRouteLoader();

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

        $this->loader->load($this->routeCollection, $actionConfiguration);

        $this->assertCount(1, $this->routeCollection->all());

        $route = $this->routeCollection->get('foo.index');

        $this->assertNotNull($route);
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
        $redirectionConfiguration = new ActionRedirectionConfiguration(
            route:      $redirectionRoute,
            parameters: new KeyStringCollection([]),
        );

        $routeConfiguration = new RouteConfiguration(
            name:         $routeName,
            path:         $routePath,
            requirements: ActionRouteRequirementCollection::createDefault(),
        );

        $viewConfiguration = new ActionViewConfiguration(
            path:      $viewPath,
            variables: new ActionViewVariablesCollection([]),
        );

        return new ActionConfiguration(
            entityClass:              $entityClass,
            action:                   $action,
            formTypeClass:            null,
            helperClass:              null,
            redirectionConfiguration: $redirectionConfiguration,
            routeConfiguration:       $routeConfiguration,
            viewConfiguration:        $viewConfiguration,
        );
    }
}
