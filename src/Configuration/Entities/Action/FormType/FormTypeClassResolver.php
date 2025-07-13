<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\FormType;

use Jmf\CrudEngine\Configuration\Schema\Schema;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Symfony\Component\Form\FormTypeInterface;
use Webmozart\Assert\Assert;

readonly class FormTypeClassResolver
{
    public function __construct(
        private SchemaValueExpander $schemaValueExpander,
    ) {
    }

    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @return null|class-string<FormTypeInterface>
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function resolve(
        Schema $schema,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?string {
        if (array_key_exists('formType', $actionConfig)) {
            $formTypeClass = $actionConfig['formType'];
        } else {
            $formTypeClass = $this->tryGetFallBackFormTypeClass(
                $schema,
                $entityClass,
                $action,
            );
        }

        if (null === $formTypeClass) {
            return null;
        }

        Assert::string($formTypeClass);
        Assert::classExists($formTypeClass);
        Assert::subclassOf($formTypeClass, FormTypeInterface::class);

        return $formTypeClass;
    }

    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     *
     * @return null|class-string<FormTypeInterface>
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function tryGetFallBackFormTypeClass(
        Schema $schema,
        string $entityClass,
        string $action,
    ): ?string {
        $classes = $schema->getFormTypeSchema()->expand(
            $this->schemaValueExpander,
            [
                'entityClass' => $entityClass,
                'action'      => $action,
            ],
        );

        foreach ($classes as $class) {
            if (class_exists($class)) {
                Assert::subclassOf($class, FormTypeInterface::class);

                return $class;
            }
        }

        return null;
    }
}
