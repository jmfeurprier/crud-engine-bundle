<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Functional;

use Override;

/**
 * Boots the bundle configured via the `paths` directory loader (instead of inline `entities`),
 * to cover that a flat per-entity file is discovered, mapped to its FQCN (filename + base
 * namespace) and compiled into the registry.
 */
final class PathsTestKernel extends TestKernel
{
    #[Override]
    public function getProjectDir(): string
    {
        return sys_get_temp_dir() . '/jmf_crud_engine_paths_test';
    }

    #[Override]
    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/jmf_crud_engine_paths_test/cache';
    }

    #[Override]
    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/jmf_crud_engine_paths_test/log';
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function crudEngineExtensionConfig(): array
    {
        return [
            'paths' => [
                [
                    'path'      => __DIR__ . '/../Fixtures/crud_paths',
                    'namespace' => 'Jmf\\CrudEngine\\Tests\\Fixtures',
                ],
            ],
        ];
    }
}
