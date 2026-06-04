<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Controller\Helpers;

use Doctrine\Instantiator\InstantiatorInterface;
use Doctrine\Persistence\ObjectManager;
use Jmf\CrudEngine\Controller\Helpers\CreateActionHelperDefault;
use Jmf\CrudEngine\Exception\CrudEnginePersistenceException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

final class CreateActionHelperDefaultTest extends TestCase
{
    public function testPersistWrapsDoctrineFailure(): void
    {
        $objectManager = $this->createStub(ObjectManager::class);
        $objectManager->method('flush')->willThrowException(new RuntimeException('boom'));

        $createActionHelperDefault = new CreateActionHelperDefault($this->createStub(InstantiatorInterface::class));

        $this->expectException(CrudEnginePersistenceException::class);

        $createActionHelperDefault->persist(
            new Request(),
            new stdClass(),
            $this->createStub(FormInterface::class),
            $objectManager,
        );
    }
}
