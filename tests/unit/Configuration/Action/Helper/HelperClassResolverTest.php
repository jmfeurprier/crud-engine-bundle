<?php

namespace Jmf\CrudEngine\Tests\Configuration\Action\Helper;

use Jmf\CrudEngine\Configuration\Entities\Action\Helper\HelperClassResolver;
use Jmf\CrudEngine\Configuration\Schema\Route\SchemaRouteConfiguration;
use Jmf\CrudEngine\Configuration\Schema\SchemaConfiguration;
use Jmf\CrudEngine\Configuration\Schema\View\SchemaViewConfiguration;
use Jmf\CrudEngine\Configuration\Schema\View\Variables\SchemaViewVariablesCollection;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class HelperClassResolverTest extends TestCase
{
    private HelperClassResolver $helperClassResolver;

    protected function setUp(): void
    {
        $this->helperClassResolver = new HelperClassResolver();
    }

    /**
     * @return array{
     *     0: non-empty-string,
     *     1: non-empty-string,
     *     2: array<string, mixed>,
     *     3: non-empty-string,
     * }[]
     */
    public static function dataProviderClassActionAndHelperClass(): iterable
    {
        return [
            [
                'App\\Entity\\Article',
                'create',
                [],
                'App\\Controller\\ArticleCreateActionHelper',
            ],
            [
                'App\\Entity\\Article',
                'update',
                [],
                'App\\Controller\\Article\\UpdateActionHelper',
            ],
        ];
    }

    /**
     * @param class-string         $class
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     * @param class-string         $helperClass
     *
     * @throws Exception
     */
    #[DataProvider('dataProviderClassActionAndHelperClass')]
    public function testLoad(
        string $class,
        string $action,
        array $actionConfig,
        string $helperClass,
    ): void {
        $schemaConfiguration = new SchemaConfiguration(
            new SchemaRouteConfiguration('foo'),
            new SchemaViewConfiguration('bar', SchemaViewVariablesCollection::createEmpty()),
        );

        $result = $this->helperClassResolver->resolve($schemaConfiguration, $class, $action, $actionConfig);

        self::assertNull($result);

        $newClass = $this->createMock(ActionHelperInterface::class);
        class_alias($newClass::class, $helperClass);

        $result = $this->helperClassResolver->resolve($schemaConfiguration, $class, $action, $actionConfig);

        self::assertSame($helperClass, $result);
    }
}
