<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Configuration\Entities\Action\FormType;

use Jmf\CrudEngine\Configuration\Entities\Action\FormType\ActionFormTypeClassResolver;
use Jmf\CrudEngine\Configuration\Schema\FormType\FormTypeSchema;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\TemplateRendering\TemplateRenderer;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormTypeInterface;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

final class ActionFormTypeClassResolverTest extends TestCase
{
    private ActionFormTypeClassResolver $actionFormTypeClassResolver;

    #[Override]
    protected function setUp(): void
    {
        $twigEnvironment = new Environment(new ArrayLoader());
        $twigEnvironment->addExtension(new StringExtension());

        $this->actionFormTypeClassResolver = new ActionFormTypeClassResolver(
            new SchemaValueExpander(new TemplateRenderer($twigEnvironment)),
        );
    }

    /**
     * @return array{
     *     0: array<non-empty-string, non-empty-string>,
     *     1: non-empty-string,
     *     2: non-empty-string,
     *     3: array<string, mixed>,
     *     4: null|non-empty-string,
     * }[]
     */
    public static function dataProviderClassActionAndFormTypeClass(): iterable
    {
        return [
            [
                [
                    'ActionKey' => 'Create',
                    'EntityKey' => 'Article',
                ],
                'App\\Entity\\Article',
                'create',
                [],
                'App\\Form\\ArticleCreateType',
            ],
            [
                [
                    'ActionKey' => 'Update',
                    'EntityKey' => 'Article',
                ],
                'App\\Entity\\Article',
                'update',
                [],
                'App\\Form\\Article\\UpdateType',
            ],
            [
                [
                    'ActionKey' => 'Update',
                    'EntityKey' => 'Article',
                ],
                'App\\Entity\\Article',
                'update',
                [
                    'formType' => null,
                ],
                null,
            ],
            [
                [
                    'ActionKey' => 'Create',
                    'EntityKey' => 'Article',
                ],
                'App\\Entity\\Foo',
                'create',
                [
                    'formType' => 'Bar\\Baz',
                ],
                'Bar\\Baz',
            ],
        ];
    }

    /**
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $actionConfig
     * @param class-string                              $formTypeClass
     *
     * @throws Exception
     * @throws CrudEngineInvalidConfigurationException
     */
    #[DataProvider('dataProviderClassActionAndFormTypeClass')]
    public function testLoad(
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
        ?string $formTypeClass,
    ): void {
        $formTypeSchema = FormTypeSchema::createDefault();

        if (null !== $formTypeClass && !class_exists($formTypeClass)) {
            $newClass = $this->createMock(FormTypeInterface::class);
            class_alias($newClass::class, $formTypeClass);
        }

        $result = $this->actionFormTypeClassResolver->resolve(
            $formTypeSchema,
            $keys,
            $entityClass,
            $action,
            $actionConfig,
        );

        self::assertSame($formTypeClass, $result);
    }
}
