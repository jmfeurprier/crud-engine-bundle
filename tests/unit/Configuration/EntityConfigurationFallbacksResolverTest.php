<?php

namespace Jmf\CrudEngine\Tests\Configuration;

use Jmf\CrudEngine\Configuration\EntityConfigurationFallbacksResolver;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\String\Inflector\EnglishInflector;

class EntityConfigurationFallbacksResolverTest extends TestCase
{
    private EntityConfigurationFallbacksResolver $entityConfigurationFallbacksResolver;

    #[Override]
    protected function setUp(): void
    {
        $this->entityConfigurationFallbacksResolver = new EntityConfigurationFallbacksResolver(
            new EnglishInflector(),
        );
    }

    /**
     * @return array{0: non-empty-string, 1: non-empty-string, 2: non-empty-string}[]
     */
    public static function dataProviderClassActionAndHelperClass(): iterable
    {
        return [
            [
                'App\\Entity\\Article',
                'create',
                'App\\Controller\\ArticleCreateActionHelper',
            ],
            [
                'App\\Entity\\Article',
                'update',
                'App\\Controller\\Article\\UpdateActionHelper',
            ],
        ];
    }

    /**
     * @param class-string     $class
     * @param non-empty-string $action
     * @param class-string     $helperClass
     */
    #[DataProvider('dataProviderClassActionAndHelperClass')]
    public function testTryResolveHelperClass(
        string $class,
        string $action,
        string $helperClass,
    ): void {
        $result = $this->entityConfigurationFallbacksResolver->tryResolveHelperClass($class, $action);

        self::assertNull($result);

        $newClass = $this->createMock(ActionHelperInterface::class);
        class_alias($newClass::class, $helperClass);

        $result = $this->entityConfigurationFallbacksResolver->tryResolveHelperClass($class, $action);

        self::assertSame($helperClass, $result);
    }

    /**
     * @return array{0: non-empty-string, 1: non-empty-string, 2: non-empty-string}[]
     */
    public static function dataProviderClassActionAndFormTypeClass(): iterable
    {
        return [
            [
                'App\\Entity\\Article',
                'create',
                'App\\Form\\ArticleCreateType',
            ],
            [
                'App\\Entity\\Article',
                'update',
                'App\\Form\\Article\\UpdateType',
            ],
        ];
    }

    /**
     * @param class-string     $class
     * @param non-empty-string $action
     * @param class-string     $formTypeClass
     */
    #[DataProvider('dataProviderClassActionAndFormTypeClass')]
    public function testTryResolveFormTypeClass(
        string $class,
        string $action,
        string $formTypeClass,
    ): void {
        $result = $this->entityConfigurationFallbacksResolver->tryResolveFormTypeClass($class, $action);

        self::assertNull($result);

        $newClass = $this->createMock(FormInterface::class);
        class_alias($newClass::class, $formTypeClass);

        $result = $this->entityConfigurationFallbacksResolver->tryResolveFormTypeClass($class, $action);

        self::assertSame($formTypeClass, $result);
    }

    /**
     * @return array{0: non-empty-string, 1: non-empty-string, 2: non-empty-string}[]
     */
    public static function dataProviderClassActionAndViewPath(): iterable
    {
        return [
            [
                'App\\Entity\\Article',
                'create',
                'article/create.html.twig',
            ],
        ];
    }

    /**
     * @param class-string     $class
     * @param non-empty-string $action
     * @param class-string     $viewPath
     */
    #[DataProvider('dataProviderClassActionAndViewPath')]
    public function testResolveViewPath(
        string $class,
        string $action,
        string $viewPath,
    ): void {
        $result = $this->entityConfigurationFallbacksResolver->resolveViewPath($class, $action);

        self::assertSame($viewPath, $result);
    }

    /**
     * @return array{0: non-empty-string, 1: non-empty-string, 2: non-empty-string}[]
     */
    public static function dataProviderClassActionAndRoutePath(): iterable
    {
        return [
            [
                'App\\Entity\\Article',
                'create',
                'articles/create',
            ],
            [
                'App\\Entity\\Article',
                'read',
                'articles/{id}',
            ],
            [
                'App\\Entity\\Article',
                'index',
                'articles',
            ],
        ];
    }

    /**
     * @param class-string     $class
     * @param non-empty-string $action
     * @param class-string     $routePath
     */
    #[DataProvider('dataProviderClassActionAndRoutePath')]
    public function testTryResolveRoutePath(
        string $class,
        string $action,
        string $routePath,
    ): void {
        $result = $this->entityConfigurationFallbacksResolver->tryResolveRoutePath($class, $action);

        self::assertSame($routePath, $result);
    }
}
