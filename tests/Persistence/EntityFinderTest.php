<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Persistence;

use Doctrine\ORM\EntityManagerInterface;
use Jmf\CrudEngine\Exception\CrudEnginePersistenceException;
use Jmf\CrudEngine\Persistence\EntityFinder;
use Jmf\CrudEngine\Persistence\EntityManagerResolver;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class EntityFinderTest extends TestCase
{
    public function testReturnsFoundEntity(): void
    {
        $entity = new stdClass();

        self::assertSame(
            $entity,
            $this->createEntityFinder($this->givenObjectManager($entity))->find(stdClass::class, '1'),
        );
    }

    public function testThrowsNotFoundWhenMissing(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->createEntityFinder($this->givenObjectManager(null))->find(stdClass::class, '1');
    }

    public function testWrapsLookupFailure(): void
    {
        $objectManager = $this->createStub(EntityManagerInterface::class);
        $objectManager->method('find')->willThrowException(new RuntimeException('boom'));

        $this->expectException(CrudEnginePersistenceException::class);

        $this->createEntityFinder($objectManager)->find(stdClass::class, '1');
    }

    private function givenObjectManager(?object $entity): EntityManagerInterface
    {
        $objectManager = $this->createStub(EntityManagerInterface::class);
        $objectManager->method('find')->willReturn($entity);

        return $objectManager;
    }

    private function createEntityFinder(EntityManagerInterface $objectManager): EntityFinder
    {
        $objectManagerResolver = $this->createStub(EntityManagerResolver::class);
        $objectManagerResolver->method('resolve')->willReturn($objectManager);

        return new EntityFinder($objectManagerResolver);
    }
}
