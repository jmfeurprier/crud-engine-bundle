<?php

namespace Jmf\CrudEngine\Tests\Configuration\Entities\Action\Helper;

use Jmf\CrudEngine\Configuration\Entities\Action\Helper\HelperClassResolver;
use Jmf\CrudEngine\Configuration\Schema\Helper\SchemaHelperConfiguration;
use Jmf\CrudEngine\Configuration\Schema\Route\SchemaRouteConfiguration;
use Jmf\CrudEngine\Configuration\Schema\SchemaConfiguration;
use Jmf\CrudEngine\Configuration\Schema\View\SchemaViewConfiguration;
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
     *     0: non-empty-string[],
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
                SchemaHelperConfiguration::DEFAULT_CLASSES,
                'App\\Entity\\Article',
                'create',
                [],
                'App\\Controller\\ArticleCreateActionHelper',
            ],
            [
                SchemaHelperConfiguration::DEFAULT_CLASSES,
                'App\\Entity\\Article',
                'update',
                [],
                'App\\Controller\\Article\\UpdateActionHelper',
            ],
        ];
    }

    /**
     * @param non-empty-string[]   $schemaHelperClasses
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
        iterable $schemaHelperClasses,
        string $entityClass,
        string $action,
        array $actionConfig,
        string $helperClass,
    ): void {
        $schemaConfiguration = new SchemaConfiguration(
            new SchemaHelperConfiguration($schemaHelperClasses),
            $this->createMock(SchemaRouteConfiguration::class),
            $this->createMock(SchemaViewConfiguration::class),
        );

        $result = $this->helperClassResolver->resolve($schemaConfiguration, $entityClass, $action, $actionConfig);

        self::assertNull($result);

        $newClass = $this->createMock(ActionHelperInterface::class);
        class_alias($newClass::class, $helperClass);

        $result = $this->helperClassResolver->resolve($schemaConfiguration, $entityClass, $action, $actionConfig);

        self::assertSame($helperClass, $result);
    }
}
