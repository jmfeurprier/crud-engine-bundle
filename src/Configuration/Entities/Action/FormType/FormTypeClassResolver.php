<?php

namespace Jmf\CrudEngine\Configuration\Entities\Action\FormType;

use Jmf\CrudEngine\Configuration\Schema\Schema;
use Symfony\Component\Form\FormTypeInterface;
use Webmozart\Assert\Assert;
use function Symfony\Component\String\u;

readonly class FormTypeClassResolver
{
    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @return null|class-string<FormTypeInterface>
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
     * @todo Retrieve from schema instead.
     */
    private function tryGetFallBackFormTypeClass(
        Schema $schema,
        string $entityClass,
        string $action,
    ): ?string {
        $classShortName  = u($entityClass)->afterLast('\\')->toString();
        $actionCamelName = u($action)->camel()->title()->toString();

        $candidates = [
            "App\\Form\\{$classShortName}\\{$actionCamelName}Type",
            "App\\Form\\{$classShortName}{$actionCamelName}Type",
            "App\\Form\\{$classShortName}Type",
        ];

        foreach ($candidates as $candidate) {
            if (class_exists($candidate)) {
                Assert::subclassOf($candidate, FormTypeInterface::class);

                return $candidate;
            }
        }

        return null;
    }
}
