<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Configuration\Entities\Action\Helper;

use Jmf\CrudEngine\Configuration\Entities\Action\Helper\HelperClassResolver;
use Jmf\CrudEngine\Configuration\Schema\Helper\HelperSchema;
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

final class HelperClassResolverTest extends TestCase
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
        HelperSchema $helperSchema,
        string $entityClass,
        string $action,
        array $actionConfig,
        string $helperClass,
    ): void {
        $result = $this->helperClassResolver->resolve(
            $helperSchema,
            [],
            $entityClass,
            $action,
            $actionConfig,
        );

        $this->assertNull($result);

        $newClass = $this->createMock(ActionHelperInterface::class);
        class_alias($newClass::class, $helperClass);

        $result = $this->helperClassResolver->resolve(
            $helperSchema,
            [],
            $entityClass,
            $action,
            $actionConfig,
        );

        $this->assertSame($helperClass, $result);
    }
}
