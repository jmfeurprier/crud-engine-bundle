<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Functional;

use Jmf\CrudEngine\Model\CrudAction;
use Jmf\CrudEngine\Registry\ActionDefinitionRegistry;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use Override;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Boots the bundle through the `paths` directory loader: a flat file tests/Fixtures/crud_paths/
 * Article.yaml (body = `actions:` only) must be discovered and mapped to
 * Jmf\CrudEngine\Tests\Fixtures\Article via the configured base namespace.
 */
final class PathsLoadingTest extends KernelTestCase
{
    #[Override]
    protected static function getKernelClass(): string
    {
        return PathsTestKernel::class;
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

        restore_exception_handler();
    }

    public function testEntityIsLoadedFromPerEntityFile(): void
    {
        $registry = self::getContainer()->get(ActionDefinitionRegistry::class);
        self::assertInstanceOf(ActionDefinitionRegistry::class, $registry);

        $definition = $registry->get(Article::class, CrudAction::Read);

        self::assertSame('article.read', $definition->getRouteDefinition()->getName());
    }
}
