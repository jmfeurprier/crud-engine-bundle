<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Configuration;

use Jmf\CrudEngine\Configuration\EntityConfigLoader;
use Jmf\CrudEngine\Exception\CrudEngineDuplicateEntityException;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EntityConfigLoaderTest extends TestCase
{
    private const string FIXTURES_DIR = __DIR__ . '/../Fixtures/crud_paths';

    private const string FIXTURES_NAMESPACE = 'Jmf\\CrudEngine\\Tests\\Fixtures';

    public function testMapsFilenameAndNamespaceToFqcnAndNormalizesEmptyAction(): void
    {
        $container = new ContainerBuilder();

        $entities = (new EntityConfigLoader())->load(
            $this->config([['path' => self::FIXTURES_DIR, 'namespace' => self::FIXTURES_NAMESPACE]]),
            $container,
            'jmf_crud_engine',
        );

        // Article.yaml body is `actions: { read: }` -> the empty-action null is normalized to [].
        self::assertSame(
            [
                Article::class => ['actions' => ['read' => []]],
            ],
            $entities,
        );

        // The directory is tracked so the compiled container rebuilds on file changes.
        $directoryResources = array_filter(
            $container->getResources(),
            static fn (object $resource): bool => $resource instanceof DirectoryResource,
        );
        self::assertNotEmpty($directoryResources);
    }

    public function testInlineEntitiesAreMerged(): void
    {
        $entities = (new EntityConfigLoader())->load(
            $this->config(
                [['path' => self::FIXTURES_DIR, 'namespace' => self::FIXTURES_NAMESPACE]],
                ['App\\Entity\\Other' => ['actions' => ['index' => []]]],
            ),
            new ContainerBuilder(),
            'jmf_crud_engine',
        );

        self::assertArrayHasKey(Article::class, $entities);
        self::assertArrayHasKey('App\\Entity\\Other', $entities);
    }

    public function testMissingDirectoryYieldsOnlyInline(): void
    {
        $entities = (new EntityConfigLoader())->load(
            $this->config([['path' => __DIR__ . '/does-not-exist', 'namespace' => 'App\\Entity']]),
            new ContainerBuilder(),
            'jmf_crud_engine',
        );

        self::assertSame([], $entities);
    }

    public function testDuplicateBetweenFileAndInlineThrows(): void
    {
        $this->expectException(CrudEngineDuplicateEntityException::class);

        (new EntityConfigLoader())->load(
            $this->config(
                [['path' => self::FIXTURES_DIR, 'namespace' => self::FIXTURES_NAMESPACE]],
                [Article::class => ['actions' => ['read' => []]]],
            ),
            new ContainerBuilder(),
            'jmf_crud_engine',
        );
    }

    public function testDuplicateFqcnAcrossPathsThrows(): void
    {
        $this->expectException(CrudEngineDuplicateEntityException::class);

        (new EntityConfigLoader())->load(
            $this->config(
                [
                    ['path' => self::FIXTURES_DIR, 'namespace' => self::FIXTURES_NAMESPACE],
                    ['path' => self::FIXTURES_DIR, 'namespace' => self::FIXTURES_NAMESPACE],
                ],
            ),
            new ContainerBuilder(),
            'jmf_crud_engine',
        );
    }

    /**
     * @param list<array{path: string, namespace: string}> $paths
     * @param array<string, mixed>                         $entities
     *
     * @return array<string, mixed>
     */
    private function config(
        array $paths,
        array $entities = [],
    ): array {
        return [
            'paths'    => $paths,
            'entities' => $entities,
        ];
    }
}
