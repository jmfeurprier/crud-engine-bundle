<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Configuration\Entities\Action\Helper;

use Jmf\CrudEngine\Configuration\Entities\Action\Helper\ActionHelperClassResolver;
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

final class ActionHelperClassResolverTest extends TestCase
{
    private ActionHelperClassResolver $actionHelperClassResolver;

    protected function setUp(): void
    {
        $twigEnvironment = new Environment(new ArrayLoader());
        $twigEnvironment->addExtension(new StringExtension());

        $this->actionHelperClassResolver = new ActionHelperClassResolver(
            new SchemaValueExpander(new TemplateRenderer($twigEnvironment)),
        );
    }

    /**
     * @return array{
     *     0: HelperSchema,
     *     1: array<non-empty-string, non-empty-string>,
     *     2: non-empty-string,
     *     3: non-empty-string,
     *     4: array<string, mixed>,
     *     5: non-empty-string,
     * }[]
     */
    public static function dataProviderClassActionAndHelperClass(): iterable
    {
        return [
            [
                HelperSchema::createDefault(),
                [
                    'ActionKey' => 'Create',
                    'EntityKey' => 'Article',
                ],
                'App\\Entity\\Article',
                'create',
                [],
                'App\\Controller\\ArticleCreateActionHelper',
            ],
            [
                HelperSchema::createDefault(),
                [
                    'ActionKey' => 'Update',
                    'EntityKey' => 'Article',
                ],
                'App\\Entity\\Article',
                'update',
                [],
                'App\\Controller\\Article\\UpdateActionHelper',
            ],
        ];
    }

    /**
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $actionConfig
     * @param class-string                              $helperClass
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws Exception
     */
    #[DataProvider('dataProviderClassActionAndHelperClass')]
    public function testLoad(
        HelperSchema $helperSchema,
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
        ?string $helperClass,
    ): void {
        $result = $this->actionHelperClassResolver->resolve(
            $helperSchema,
            $keys,
            $entityClass,
            $action,
            $actionConfig,
        );

        self::assertNull($result);

        if (null !== $helperClass) {
            $newClass = $this->createStub(ActionHelperInterface::class);
            class_alias($newClass::class, $helperClass);

            $result = $this->actionHelperClassResolver->resolve(
                $helperSchema,
                $keys,
                $entityClass,
                $action,
                $actionConfig,
            );

            self::assertSame($helperClass, $result);
        }
    }
}
