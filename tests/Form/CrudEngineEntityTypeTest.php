<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Form;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\FieldMapping;
use Jmf\CrudEngine\Exception\CrudEngineFormFieldException;
use Jmf\CrudEngine\Form\CrudEngineEntityType;
use Jmf\CrudEngine\Form\FieldGenerator;
use Jmf\CrudEngine\Persistence\EntityManagerResolver;
use Jmf\CrudEngine\Tests\Fixtures\Article;
use Jmf\CrudEngine\Tests\Fixtures\Status;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class CrudEngineEntityTypeTest extends TestCase
{
    public function testBuildsFieldsFromMetadata(): void
    {
        $fieldMapping           = new FieldMapping('string', 'status', 'status');
        $fieldMapping->enumType = Status::class;

        $bodyMapping           = new FieldMapping('text', 'body', 'body');
        $bodyMapping->nullable = true;

        $metadata = $this->createStub(ClassMetadata::class);
        $metadata->method('getIdentifierFieldNames')->willReturn(['id']);
        $metadata->method('getFieldNames')->willReturn(
            [
                'id',
                'title',
                'body',
                'status',
                'birthDate.year',
            ],
        );
        $metadata->method('getFieldMapping')->willReturnMap(
            [
                [
                    'title',
                    new FieldMapping('string', 'title', 'title'),
                ],
                [
                    'body',
                    $bodyMapping,
                ],
                [
                    'status',
                    $fieldMapping,
                ],
            ],
        );

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getClassMetadata')->willReturn($metadata);

        $entityManagerResolver = $this->createStub(EntityManagerResolver::class);
        $entityManagerResolver->method('resolve')->willReturn($entityManager);

        /** @var array<string, array{type: string, options: array<string, mixed>}> $added */
        $added   = [];
        $builder = $this->createStub(FormBuilderInterface::class);
        $builder
            ->method('add')
            ->willReturnCallback(
                static function (
                    string $child,
                    string $type,
                    array $options,
                ) use
                (
                    &
                    $added,
                    $builder,
                ): FormBuilderInterface {
                    $added[$child] =
                        [
                            'type'    => $type,
                            'options' => $options,
                        ];

                    return $builder;
                },
            )
        ;

        (new CrudEngineEntityType($entityManagerResolver, new FieldGenerator()))->buildForm(
            $builder,
            [
                'entity_class'              => Article::class,
                'suggested_form_type_class' => 'StubFormType',
            ],
        );

        // The fallback notice is added first; identifier, embeddable sub-field and entity association are skipped.
        self::assertSame(
            [
                '_crudEngineFallbackNotice',
                'title',
                'body',
                'status',
            ],
            array_keys($added),
        );

        self::assertSame(TextType::class, $added['_crudEngineFallbackNotice']['type']);
        self::assertFalse($added['_crudEngineFallbackNotice']['options']['mapped']);
        self::assertTrue($added['_crudEngineFallbackNotice']['options']['disabled']);
        self::assertSame('StubFormType', $added['_crudEngineFallbackNotice']['options']['data']);

        self::assertSame(TextType::class, $added['title']['type']);
        self::assertTrue($added['title']['options']['required']);

        self::assertSame(TextareaType::class, $added['body']['type']);
        self::assertFalse($added['body']['options']['required']);

        self::assertSame(EnumType::class, $added['status']['type']);
        self::assertSame(Status::class, $added['status']['options']['class']);
    }

    public function testWrapsMetadataFailure(): void
    {
        $metadata = $this->createStub(ClassMetadata::class);
        $metadata->method('getName')->willReturn(Article::class);
        $metadata->method('getIdentifierFieldNames')->willReturn(['id']);
        $metadata->method('getFieldNames')->willReturn(['id', 'title']);
        $metadata->method('getFieldMapping')->willThrowException(new RuntimeException('boom'));

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getClassMetadata')->willReturn($metadata);

        $entityManagerResolver = $this->createStub(EntityManagerResolver::class);
        $entityManagerResolver->method('resolve')->willReturn($entityManager);

        $this->expectException(CrudEngineFormFieldException::class);

        (new CrudEngineEntityType($entityManagerResolver, new FieldGenerator()))->buildForm(
            $this->createStub(FormBuilderInterface::class),
            [
                'entity_class'              => Article::class,
                'suggested_form_type_class' => 'StubFormType',
            ],
        );
    }
}
