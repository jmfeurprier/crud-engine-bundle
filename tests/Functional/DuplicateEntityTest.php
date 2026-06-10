<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Functional;

use Jmf\CrudEngine\Exception\CrudEngineDuplicateEntityException;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use Override;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * An entity defined both via `paths` and inline under `entities` must fail at container build time.
 */
final class DuplicateEntityTest extends KernelTestCase
{
    #[Override]
    protected static function getKernelClass(): string
    {
        return DuplicateEntityTestKernel::class;
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

    public function testDuplicateEntityBetweenPathsAndInlineThrows(): void
    {
        try {
            self::bootKernel();

            self::fail('Expected a CrudEngineDuplicateEntityException.');
        } catch (CrudEngineDuplicateEntityException $exception) {
            self::assertSame([Article::class], $exception->getEntityClasses());
        }
    }
}
