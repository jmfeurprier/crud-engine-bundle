<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Form;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\FieldMapping;
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
        FieldMapping $fieldMapping,
    ): ?GeneratedField {
        if (!$this->isMappable($fieldMapping)) {
            return null;
        }

        return new GeneratedField(
            $this->getTypeClass($fieldMapping),
            $this->getOptions($fieldMapping),
        );
    }

    private function isMappable(
        FieldMapping $fieldMapping,
    ): bool {
        if ($this->isEnum($fieldMapping)) {
            return true;
        }

        return array_key_exists(
            $fieldMapping->type,
            self::TYPE_MAPPING,
        );
    }

    /**
     * @return class-string<FormTypeInterface>
     */
    private function getTypeClass(
        FieldMapping $fieldMapping,
    ): string {
        if ($this->isEnum($fieldMapping)) {
            return EnumType::class;
        }

        return self::TYPE_MAPPING[$fieldMapping->type];
    }

    /**
     * @return array<string, mixed>
     */
    private function getOptions(
        FieldMapping $fieldMapping,
    ): array {
        if ($this->isEnum($fieldMapping)) {
            return [
                'class' => $fieldMapping->enumType,
                //'required' => $required,
            ];
        }

        return match ($fieldMapping->type) {
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
        FieldMapping $fieldMapping,
    ): bool {
        return (null !== $fieldMapping->enumType);
    }
}
