<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Functional;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\JmfCrudEngineBundle;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use Override;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Minimal kernel that boots the bundle with a sample configuration, to exercise the wiring that
 * unit tests cannot reach: services.yaml, the bundle extension (config compilation + dump),
 * config/definition.php (the schema tree) and the route loader.
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    #[Override]
    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new TwigBundle();
        yield new JmfCrudEngineBundle();
    }

    #[Override]
    public function getProjectDir(): string
    {
        // Keep kernel-generated artifacts (cache, and Symfony's auto-generated config/reference.php)
        // out of the bundle: use a temp project dir instead of the bundle root.
        return sys_get_temp_dir() . '/jmf_crud_engine_test';
    }

    #[Override]
    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/jmf_crud_engine_test/cache';
    }

    #[Override]
    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/jmf_crud_engine_test/log';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret'               => 'test',
            'test'                 => true,
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'php_errors'           => ['log' => false],
            'validation'           => ['enabled' => false],
            'csrf_protection'      => false,
            'form'                 => ['csrf_protection' => false],
            'router'               => ['utf8' => true],
        ]);

        $container->extension('twig', []);

        $container->extension('jmf_crud_engine', [
            'entities' => [
                Article::class => [
                    'actions' => [
                        'index'  => [],
                        'read'   => [],
                        'create' => [],
                        'update' => [],
                        'delete' => [],
                    ],
                ],
            ],
        ]);

        // No DoctrineBundle here: EntityManagerResolver depends on a ManagerRegistry, but the wiring
        // and route-loader paths never resolve it, so a synthetic placeholder is enough to compile.
        $container->services()
            ->set(ManagerRegistry::class)
            ->synthetic()
        ;
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
    }
}
