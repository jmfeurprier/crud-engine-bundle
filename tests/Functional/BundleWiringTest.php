<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Functional;

use Jmf\CrudEngine\Model\CrudAction;
use Jmf\CrudEngine\Registry\ActionDefinitionRegistry;
use Jmf\CrudEngine\Routing\RouteLoader;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use Override;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Boots the bundle in a real container to cover what the unit tests cannot: services.yaml wiring,
 * the bundle extension (config compilation + dump into the container) and config/definition.php.
 */
final class BundleWiringTest extends KernelTestCase
{
    #[Override]
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    protected static function createKernel(array $options = []): KernelInterface
    {
        $options['debug'] ??= false;

        return parent::createKernel($options);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Booting the kernel registers a Symfony exception handler it never removes; restore it so
        // PHPUnit does not flag the test as risky. (Only the exception handler — PHPUnit owns the
        // error handler.)
        restore_exception_handler();
    }

    public function testContainerCompilesAndRegistryResolvesADefinition(): void
    {
        $registry = self::getContainer()->get(ActionDefinitionRegistry::class);
        self::assertInstanceOf(ActionDefinitionRegistry::class, $registry);

        $definition = $registry->get(Article::class, CrudAction::Read);

        self::assertSame('article.read', $definition->getRouteDefinition()->getName());
        self::assertSame('article/read.html.twig', $definition->getViewDefinition()->getPath());
    }

    public function testRouteLoaderRegistersARouteForEachConfiguredAction(): void
    {
        $routeLoader = self::getContainer()->get(RouteLoader::class);
        self::assertInstanceOf(RouteLoader::class, $routeLoader);

        $routes = $routeLoader();

        self::assertCount(5, $routes);

        $route = $routes->get('article.read');
        self::assertNotNull($route);
        self::assertSame('/articles/{id}', $route->getPath());
    }
}
