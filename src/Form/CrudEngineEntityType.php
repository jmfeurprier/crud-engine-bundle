<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Form;

use Doctrine\ORM\Mapping\ClassMetadata;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineFormFieldException;
use Jmf\CrudEngine\Model\EntityAction;
use Jmf\CrudEngine\Persistence\EntityManagerResolver;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Throwable;
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
        private readonly EntityManagerResolver $entityManagerResolver,
        private readonly FieldGenerator $fieldGenerator,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws CrudEngineEntityManagerNotFoundException
     * @throws CrudEngineFormFieldException
     */
    #[Override]
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $entityAction = $options['entity_action'];

        Assert::isInstanceOf($entityAction, EntityAction::class);

        $suggestedFormTypeClass = $options['suggested_form_type_class'];

        Assert::string($suggestedFormTypeClass);

        $this->addFallbackNotice($builder, $suggestedFormTypeClass);

        $metadata             = $this->getClassMetadata($entityAction->getEntityClass());
        $identifierFieldNames = $metadata->getIdentifierFieldNames();

        foreach ($metadata->getFieldNames() as $fieldName) {
            if (in_array($fieldName, $identifierFieldNames, true)) {
                continue;
            }

            // Embeddable sub-fields are exposed as "embeddable.property"; skip them.
            if (str_contains($fieldName, '.')) {
                continue;
            }

            $this->addField(
                builder:      $builder,
                metadata:     $metadata,
                entityAction: $entityAction,
                fieldName:    $fieldName,
            );
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
        $resolver->setRequired('entity_action');
        $resolver->setAllowedTypes('entity_action', EntityAction::class);

        $resolver->setRequired('suggested_form_type_class');
        $resolver->setAllowedTypes('suggested_form_type_class', 'string');

        $resolver->setDefault(
            'data_class',
            static function (
                Options $options,
            ): string {
                Assert::isInstanceOf($options['entity_action'], EntityAction::class);

                return $options['entity_action']->getEntityClass();
            },
        );
    }

    /**
     * @param class-string $entityClass
     *
     * @return ClassMetadata<object>
     *
     * @throws CrudEngineEntityManagerNotFoundException
     */
    private function getClassMetadata(string $entityClass): ClassMetadata
    {
        return $this->entityManagerResolver->resolve($entityClass)->getClassMetadata($entityClass);
    }

    /**
     * @param ClassMetadata<object> $metadata
     *
     * @throws CrudEngineFormFieldException
     */
    private function addField(
        FormBuilderInterface $builder,
        ClassMetadata $metadata,
        EntityAction $entityAction,
        string $fieldName,
    ): void {
        try {
            $fieldMapping = $metadata->getFieldMapping($fieldName);
        } catch (Throwable $e) {
            throw new CrudEngineFormFieldException(
                entityAction: $entityAction,
                previous:     $e,
            );
        }

        $generatedField = $this->fieldGenerator->generate($fieldMapping);

        if (!$generatedField instanceof GeneratedField) {
            return;
        }

        $builder->add(
            $fieldName,
            $generatedField->getTypeClass(),
            $generatedField->getOptions(),
        );
    }
}
