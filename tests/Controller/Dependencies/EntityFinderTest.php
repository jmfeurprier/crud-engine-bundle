<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Controller\Dependencies;

use Doctrine\Persistence\ObjectManager;
use Jmf\CrudEngine\Controller\Dependencies\EntityFinder;
use Jmf\CrudEngine\Controller\Dependencies\EntityManagerResolver;
use Jmf\CrudEngine\Exception\CrudEnginePersistenceException;
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
        $objectManager = $this->createStub(ObjectManager::class);
        $objectManager->method('find')->willThrowException(new RuntimeException('boom'));

        $this->expectException(CrudEnginePersistenceException::class);

        $this->createEntityFinder($objectManager)->find(stdClass::class, '1');
    }

    private function givenObjectManager(?object $entity): ObjectManager
    {
        $objectManager = $this->createStub(ObjectManager::class);
        $objectManager->method('find')->willReturn($entity);

        return $objectManager;
    }

    private function createEntityFinder(ObjectManager $objectManager): EntityFinder
    {
        $entityManagerResolver = $this->createStub(EntityManagerResolver::class);
        $entityManagerResolver->method('resolve')->willReturn($objectManager);

        return new EntityFinder($entityManagerResolver);
    }
}
