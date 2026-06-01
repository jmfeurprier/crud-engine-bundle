<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Form;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\ManagerRegistry;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

/**
 * Generic form built from an entity's Doctrine metadata, used as a fallback when no form
 * type is configured or discovered for an action. It is a scaffold to be overridden:
 * it maps scalar fields and `enumType` fields. Identifiers, embeddables, entity associations
 * and unmappable column types are skipped.
 */
class CrudEngineEntityType extends AbstractType
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws MappingException
     */
    #[Override]
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $entityClass = $options['entity_class'];

        Assert::string($entityClass);
        Assert::classExists($entityClass);

        $metadata             = $this->getClassMetadata($entityClass);
        $identifierFieldNames = $metadata->getIdentifierFieldNames();

        foreach ($metadata->getFieldNames() as $fieldName) {
            if (in_array($fieldName, $identifierFieldNames, true)) {
                continue;
            }

            // Embeddable sub-fields are exposed as "embeddable.property"; skip them.
            if (str_contains($fieldName, '.')) {
                continue;
            }

            $this->addField($builder, $metadata, $fieldName);
        }
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('entity_class');
        $resolver->setAllowedTypes('entity_class', 'string');
        $resolver->setDefault(
            'data_class',
            static function (
                Options $options,
            ): string {
                Assert::string($options['entity_class']);

                return $options['entity_class'];
            },
        );
    }

    /**
     * @param class-string $entityClass
     *
     * @return ClassMetadata<object>
     */
    private function getClassMetadata(string $entityClass): ClassMetadata
    {
        $manager = $this->managerRegistry->getManagerForClass($entityClass);

        Assert::isInstanceOf($manager, EntityManagerInterface::class);

        return $manager->getClassMetadata($entityClass);
    }

    /**
     * @param ClassMetadata<object> $metadata
     *
     * @throws MappingException
     */
    private function addField(
        FormBuilderInterface $builder,
        ClassMetadata $metadata,
        string $fieldName,
    ): void {
        $required = !$metadata->isNullable($fieldName);

        $enumType = $metadata->getFieldMapping($fieldName)->enumType;

        if (null !== $enumType) {
            $builder->add(
                $fieldName,
                EnumType::class,
                [
                    'class'    => $enumType,
                    'required' => $required,
                ],
            );

            return;
        }

        $mapping = $this->mapType($metadata->getTypeOfField($fieldName));

        if (null === $mapping) {
            return;
        }

        [
            $formType,
            $options,
        ] = $mapping;

        if (CheckboxType::class === $formType) {
            $required = false;
        }

        $builder->add($fieldName, $formType, ['required' => $required] + $options);
    }

    /**
     * @return array{0: class-string<FormTypeInterface>, 1: array<string, mixed>}|null
     */
    private function mapType(?string $doctrineType): ?array
    {
        return match ($doctrineType) {
            Types::STRING, Types::ASCII_STRING, Types::GUID        => [
                TextType::class,
                [],
            ],
            Types::TEXT                                            => [
                TextareaType::class,
                [],
            ],
            Types::INTEGER, Types::SMALLINT, Types::BIGINT         => [
                IntegerType::class,
                [],
            ],
            Types::BOOLEAN                                         => [
                CheckboxType::class,
                [],
            ],
            Types::FLOAT, Types::DECIMAL                           => [
                NumberType::class,
                [],
            ],
            Types::DATE_MUTABLE                                    => [
                DateType::class,
                ['widget' => 'single_text'],
            ],
            Types::DATE_IMMUTABLE                                  => [
                DateType::class,
                [
                    'widget' => 'single_text',
                    'input'  => 'datetime_immutable',
                ],
            ],
            Types::DATETIME_MUTABLE, Types::DATETIMETZ_MUTABLE     => [
                DateTimeType::class,
                ['widget' => 'single_text'],
            ],
            Types::DATETIME_IMMUTABLE, Types::DATETIMETZ_IMMUTABLE => [
                DateTimeType::class,
                [
                    'widget' => 'single_text',
                    'input'  => 'datetime_immutable',
                ],
            ],
            Types::TIME_MUTABLE                                    => [
                TimeType::class,
                ['widget' => 'single_text'],
            ],
            Types::TIME_IMMUTABLE                                  => [
                TimeType::class,
                [
                    'widget' => 'single_text',
                    'input'  => 'datetime_immutable',
                ],
            ],
            default                                                => null,
        };
    }
}
