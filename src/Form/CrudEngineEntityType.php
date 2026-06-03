<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Form;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\ManagerRegistry;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
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
    private const string FALLBACK_NOTICE_FIELD = '_crudEngineFallbackNotice';

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly FieldGenerator $fieldGenerator,
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

        $suggestedFormTypeClass = $options['suggested_form_type_class'];

        Assert::string($suggestedFormTypeClass);

        $this->addFallbackNotice($builder, $suggestedFormTypeClass);

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

    /**
     * Adds a non-editable field flagging that this form was generated as a fallback,
     * pointing at the form type class to implement in order to replace it.
     */
    private function addFallbackNotice(
        FormBuilderInterface $builder,
        string $suggestedFormTypeClass,
    ): void {
        $builder->add(
            self::FALLBACK_NOTICE_FIELD,
            TextType::class,
            [
                'mapped'   => false,
                'disabled' => true,
                'required' => false,
                'label'    => 'Generated fallback form',
                'help'     => 'Generated from the entity metadata; implement the form type below to replace it.',
                'data'     => $suggestedFormTypeClass,
                'attr'     => [
                    'readonly' => true,
                ],
            ],
        );
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('entity_class');
        $resolver->setAllowedTypes('entity_class', 'string');
        $resolver->setRequired('suggested_form_type_class');
        $resolver->setAllowedTypes('suggested_form_type_class', 'string');
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
        $required     = !$metadata->isNullable($fieldName);
        $fieldMapping = $metadata->getFieldMapping($fieldName);

        $generatedField = $this->fieldGenerator->generate($fieldMapping);

        if (null === $generatedField) {
            return;
        }

        $builder->add(
            $fieldName,
            $generatedField->getTypeClass(),
            $generatedField->getOptions() + ['required' => $required],
        );
    }
}
