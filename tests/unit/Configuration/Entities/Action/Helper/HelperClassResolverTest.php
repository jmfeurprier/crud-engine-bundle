<?php

namespace Jmf\CrudEngine\Tests\Configuration\Entities\Action\Helper;

use Jmf\CrudEngine\Configuration\Entities\Action\Helper\HelperClassResolver;
use Jmf\CrudEngine\Configuration\Schema\Helper\SchemaHelpersCollection;
use Jmf\CrudEngine\Configuration\Schema\Route\SchemaRoute;
use Jmf\CrudEngine\Configuration\Schema\Schema;
use Jmf\CrudEngine\Configuration\Schema\View\SchemaView;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperInterface;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\TemplateRendering\TemplateRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

class HelperClassResolverTest extends TestCase
{
    private HelperClassResolver $helperClassResolver;

    protected function setUp(): void
    {
        $twigEnvironment = new Environment(new ArrayLoader());
        $twigEnvironment->addExtension(new StringExtension());

        $this->helperClassResolver = new HelperClassResolver(
            new SchemaValueExpander(new TemplateRenderer($twigEnvironment)),
        );
    }

    /**
     * @return array{
     *     0: SchemaHelpersCollection,
     *     1: non-empty-string,
     *     2: non-empty-string,
     *     3: array<string, mixed>,
     *     4: non-empty-string,
     * }[]
     */
    public static function dataProviderClassActionAndHelperClass(): iterable
    {
        return [
            [
                SchemaHelpersCollection::createDefault(),
                'App\\Entity\\Article',
                'create',
                [],
                'App\\Controller\\ArticleCreateActionHelper',
            ],
            [
                SchemaHelpersCollection::createDefault(),
                'App\\Entity\\Article',
                'update',
                [],
                'App\\Controller\\Article\\UpdateActionHelper',
            ],
        ];
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     * @param class-string         $helperClass
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws Exception
     */
    #[DataProvider('dataProviderClassActionAndHelperClass')]
    public function testLoad(
        SchemaHelpersCollection $schemaHelpers,
        string $entityClass,
        string $action,
        array $actionConfig,
        string $helperClass,
    ): void {
        $schema = new Schema(
            $schemaHelpers,
            $this->createMock(SchemaRoute::class),
            $this->createMock(SchemaView::class),
        );

        $result = $this->helperClassResolver->resolve($schema, $entityClass, $action, $actionConfig);

        self::assertNull($result);

        $newClass = $this->createMock(ActionHelperInterface::class);
        class_alias($newClass::class, $helperClass);

        $result = $this->helperClassResolver->resolve($schema, $entityClass, $action, $actionConfig);

        self::assertSame($helperClass, $result);
    }
}
