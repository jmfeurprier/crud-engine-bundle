<?php

namespace Jmf\CrudEngine\Tests\Configuration\Entities\Action\FormType;

use Jmf\CrudEngine\Configuration\Entities\Action\FormType\FormTypeClassResolver;
use Jmf\CrudEngine\Configuration\Schema\FormType\SchemaFormTypesCollection;
use Jmf\CrudEngine\Configuration\Schema\Helper\SchemaHelpersCollection;
use Jmf\CrudEngine\Configuration\Schema\Route\SchemaRoute;
use Jmf\CrudEngine\Configuration\Schema\Schema;
use Jmf\CrudEngine\Configuration\Schema\View\SchemaView;
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

class FormTypeClassResolverTest extends TestCase
{
    private FormTypeClassResolver $formTypeClassResolver;

    #[Override]
    protected function setUp(): void
    {
        $twigEnvironment = new Environment(new ArrayLoader());
        $twigEnvironment->addExtension(new StringExtension());

        $this->formTypeClassResolver = new FormTypeClassResolver(
            new SchemaValueExpander(new TemplateRenderer($twigEnvironment)),
        );

    }

    /**
     * @return array{
     *     0: non-empty-string,
     *     1: non-empty-string,
     *     2: array<string, mixed>,
     *     3: null|non-empty-string,
     * }[]
     */
    public static function dataProviderClassActionAndFormTypeClass(): iterable
    {
        return [
            [
                'App\\Entity\\Article',
                'create',
                [],
                'App\\Form\\ArticleCreateType',
            ],
            [
                'App\\Entity\\Article',
                'update',
                [],
                'App\\Form\\Article\\UpdateType',
            ],
            [
                'App\\Entity\\Article',
                'update',
                [
                    'formType' => null,
                ],
                null,
            ],
            [
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
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     * @param class-string         $formTypeClass
     *
     * @throws Exception
     * @throws CrudEngineInvalidConfigurationException
     */
    #[DataProvider('dataProviderClassActionAndFormTypeClass')]
    public function testLoad(
        string $entityClass,
        string $action,
        array $actionConfig,
        ?string $formTypeClass,
    ): void {
        $schema = new Schema(
            SchemaFormTypesCollection::createDefault(),
            $this->createMock(SchemaHelpersCollection::class),
            $this->createMock(SchemaRoute::class),
            $this->createMock(SchemaView::class),
        );

        if (null !== $formTypeClass && !class_exists($formTypeClass)) {
            $newClass = $this->createMock(FormTypeInterface::class);
            class_alias($newClass::class, $formTypeClass);
        }

        $result = $this->formTypeClassResolver->resolve($schema, $entityClass, $action, $actionConfig);

        self::assertSame($formTypeClass, $result);
    }
}
