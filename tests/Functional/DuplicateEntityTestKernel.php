<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Functional;

use Jmf\CrudEngine\Tests\Fixtures\Article;
use Override;

/**
 * Configures the same entity both via `paths` (Fixtures/crud_paths/Article.yaml, mapped through the
 * base namespace) and inline under `entities`, to exercise the duplicate-entity guard.
 */
final class DuplicateEntityTestKernel extends TestKernel
{
    #[Override]
    public function getProjectDir(): string
    {
        return sys_get_temp_dir() . '/jmf_crud_engine_duplicate_test';
    }

    #[Override]
    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/jmf_crud_engine_duplicate_test/cache';
    }

    #[Override]
    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/jmf_crud_engine_duplicate_test/log';
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function crudEngineExtensionConfig(): array
    {
        return [
            'paths'    => [
                [
                    'path'      => __DIR__ . '/../Fixtures/crud_paths',
                    'namespace' => 'Jmf\\CrudEngine\\Tests\\Fixtures',
                ],
            ],
            'entities' => [
                Article::class => [
                    'actions' => [
                        'read' => [],
                    ],
                ],
            ],
        ];
    }
}
