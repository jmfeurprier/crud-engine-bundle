<?php

namespace Jmf\CrudEngine\Tests\Configuration\Entities\Action\Helper;

use Jmf\CrudEngine\Configuration\Entities\Action\Helper\HelperClassResolver;
use Jmf\CrudEngine\Configuration\Schema\FormType\FormTypeSchema;
use Jmf\CrudEngine\Configuration\Schema\Helper\HelperSchema;
use Jmf\CrudEngine\Configuration\Schema\Keys\KeySchema;
use Jmf\CrudEngine\Configuration\Schema\Route\RouteSchema;
use Jmf\CrudEngine\Configuration\Schema\Schema;
use Jmf\CrudEngine\Configuration\Schema\View\ViewSchema;
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
     *     0: HelperSchema,
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
                HelperSchema::createDefault(),
                'App\\Entity\\Article',
                'create',
                [],
                'App\\Controller\\ArticleCreateActionHelper',
            ],
            [
                HelperSchema::createDefault(),
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
        HelperSchema $schemaHelpers,
        string $entityClass,
        string $action,
        array $actionConfig,
        string $helperClass,
    ): void {
        $schema = new Schema(
            $this->createMock(KeySchema::class),
            $this->createMock(FormTypeSchema::class),
            $schemaHelpers,
            $this->createMock(RouteSchema::class),
            $this->createMock(ViewSchema::class),
        );

        $result = $this->helperClassResolver->resolve($schema, $entityClass, $action, $actionConfig);

        self::assertNull($result);

        $newClass = $this->createMock(ActionHelperInterface::class);
        class_alias($newClass::class, $helperClass);

        $result = $this->helperClassResolver->resolve($schema, $entityClass, $action, $actionConfig);

        self::assertSame($helperClass, $result);
    }
}
