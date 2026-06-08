<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Controller\Helpers;

use Exception;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperInterface;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\ReadActionHelperInterface;
use Jmf\CrudEngine\Definition\ActionDefinition;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperNotAnObjectException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperRetrievalException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperTypeMismatchException;
use Jmf\CrudEngine\Model\CrudAction;
use Jmf\CrudEngine\Model\EntityAction;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

final class ActionHelperResolverTest extends TestCase
{
    public function testReturnsDefaultWhenNoHelperConfigured(): void
    {
        $default = $this->createStub(ReadActionHelperInterface::class);

        self::assertSame(
            $default,
            $this->resolver($this->createStub(ContainerInterface::class))->resolve(
                ReadActionHelperInterface::class,
                $this->actionDefinition(null),
                $default,
            ),
        );
    }

    public function testResolvesConfiguredHelper(): void
    {
        $helper = $this->createStub(ReadActionHelperInterface::class);

        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')->willReturn($helper);

        self::assertSame($helper, $this->resolveConfigured($container));
    }

    public function testThrowsNotFoundWhenContainerHasNoHelper(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')->willThrowException(
            new class extends Exception implements NotFoundExceptionInterface {},
        );

        $this->expectException(CrudEngineActionHelperNotFoundException::class);

        $this->resolveConfigured($container);
    }

    public function testThrowsRetrievalOnContainerError(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')->willThrowException(
            new class extends Exception implements ContainerExceptionInterface {},
        );

        $this->expectException(CrudEngineActionHelperRetrievalException::class);

        $this->resolveConfigured($container);
    }

    public function testThrowsWhenResolvedHelperIsNotAnObject(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')->willReturn('not-an-object');

        $this->expectException(CrudEngineActionHelperNotAnObjectException::class);

        $this->resolveConfigured($container);
    }

    public function testThrowsTypeMismatchWhenWrongHelperType(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')->willReturn($this->createStub(ActionHelperInterface::class));

        $this->expectException(CrudEngineActionHelperTypeMismatchException::class);

        $this->resolveConfigured($container);
    }

    private function resolveConfigured(ContainerInterface $container): ActionHelperInterface
    {
        return $this->resolver($container)->resolve(
            ReadActionHelperInterface::class,
            $this->actionDefinition(stdClass::class),
            $this->createStub(ReadActionHelperInterface::class),
        );
    }

    private function resolver(ContainerInterface $container): ActionHelperResolver
    {
        return new ActionHelperResolver($container);
    }

    /**
     * @param class-string|null $helperClass
     */
    private function actionDefinition(?string $helperClass): ActionDefinition
    {
        $actionDefinition = $this->createStub(ActionDefinition::class);
        $actionDefinition->method('getHelperClass')->willReturn($helperClass);
        $actionDefinition->method('getEntityAction')->willReturn(
            new EntityAction(stdClass::class, CrudAction::Read),
        );

        return $actionDefinition;
    }
}
