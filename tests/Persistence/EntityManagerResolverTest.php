<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Persistence;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerTypeMismatchException;
use Jmf\CrudEngine\Persistence\EntityManagerResolver;
use PHPUnit\Framework\TestCase;
use stdClass;

final class EntityManagerResolverTest extends TestCase
{
    public function testReturnsEntityManager(): void
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);

        self::assertSame(
            $entityManager,
            $this->resolver($entityManager)->resolve(stdClass::class),
        );
    }

    public function testThrowsWhenNoManagerForClass(): void
    {
        $this->expectException(CrudEngineEntityManagerNotFoundException::class);

        $this->resolver(null)->resolve(stdClass::class);
    }

    public function testThrowsWhenManagerIsNotAnOrmEntityManager(): void
    {
        $this->expectException(CrudEngineEntityManagerTypeMismatchException::class);

        $this->resolver($this->createStub(ObjectManager::class))->resolve(stdClass::class);
    }

    private function resolver(?ObjectManager $manager): EntityManagerResolver
    {
        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->willReturn($manager);

        return new EntityManagerResolver($managerRegistry);
    }
}
