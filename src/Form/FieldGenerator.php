<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Form;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormTypeInterface;

/**
 * @todo Refactor.
 */
readonly class FieldGenerator
{
    private const array TYPE_MAPPING = [
        Types::ASCII_STRING         => TextType::class,
        Types::BIGINT               => IntegerType::class,
        Types::BOOLEAN              => CheckboxType::class,
        Types::DATE_IMMUTABLE       => DateType::class,
        Types::DATE_MUTABLE         => DateType::class,
        Types::DATETIME_IMMUTABLE   => DateTimeType::class,
        Types::DATETIME_MUTABLE     => DateTimeType::class,
        Types::DATETIMETZ_IMMUTABLE => DateTimeType::class,
        Types::DATETIMETZ_MUTABLE   => DateTimeType::class,
        Types::DECIMAL              => NumberType::class,
        Types::FLOAT                => NumberType::class,
        Types::GUID                 => TextType::class,
        Types::INTEGER              => IntegerType::class,
        Types::SMALLINT             => IntegerType::class,
        Types::STRING               => TextType::class,
        Types::TEXT                 => TextareaType::class,
        Types::TIME_IMMUTABLE       => TimeType::class,
        Types::TIME_MUTABLE         => TimeType::class,
    ];

    /**
     * @param ClassMetadata<object> $metadata
     */
    public function generate(
        ClassMetadata $metadata,
        string $fieldName,
    ): ?GeneratedField {
        if (!$this->isMappable($metadata, $fieldName)) {
            return null;
        }

        return new GeneratedField(
            $this->getTypeClass($metadata, $fieldName),
            $this->getOptions($metadata, $fieldName),
        );
    }

    private function isMappable(
        ClassMetadata $metadata,
        string $fieldName,
    ): bool {
        if ($this->isEnum($metadata, $fieldName)) {
            return true;
        }

        $doctrineType = $metadata->getTypeOfField($fieldName);

        if (null === $doctrineType) {
            return false;
        }

        return array_key_exists($doctrineType, self::TYPE_MAPPING);
    }

    /**
     * @return class-string<FormTypeInterface>
     */
    private function getTypeClass(
        ClassMetadata $metadata,
        string $fieldName,
    ): string {
        if ($this->isEnum($metadata, $fieldName)) {
            return EnumType::class;
        }

        $doctrineType = $metadata->getTypeOfField($fieldName);

        return self::TYPE_MAPPING[$doctrineType];
    }

    /**
     * @return array<string, mixed>
     */
    private function getOptions(
        ClassMetadata $metadata,
        string $fieldName,
    ): array {
        if ($this->isEnum($metadata, $fieldName)) {
            return [
                'class' => $metadata->getFieldMapping($fieldName)->enumType,
                //'required' => $required,
            ];
        }

        $doctrineType = $metadata->getTypeOfField($fieldName);

        return match ($doctrineType) {
            Types::BOOLEAN        => [
                'required' => false,
            ],
            Types::DATE_MUTABLE,
            Types::DATETIME_MUTABLE,
            Types::DATETIMETZ_MUTABLE,
            Types::TIME_MUTABLE   => [
                'widget' => 'single_text',
            ],
            Types::DATE_IMMUTABLE,
            Types::DATETIME_IMMUTABLE,
            Types::DATETIMETZ_IMMUTABLE,
            Types::TIME_IMMUTABLE => [
                'widget' => 'single_text',
                'input'  => 'datetime_immutable',
            ],
            default               => [],
        };
    }

    private function isEnum(
        ClassMetadata $metadata,
        string $fieldName,
    ): bool {
        $enumType = $metadata->getFieldMapping($fieldName)->enumType;

        return (null !== $enumType);
    }
}
