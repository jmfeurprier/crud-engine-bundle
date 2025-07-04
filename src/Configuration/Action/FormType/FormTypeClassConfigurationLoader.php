<?php

namespace Jmf\CrudEngine\Configuration\Action\FormType;

use Symfony\Component\Form\FormInterface;
use Webmozart\Assert\Assert;
use function Symfony\Component\String\u;

readonly class FormTypeClassConfigurationLoader
{
    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @return null|class-string<FormInterface>
     */
    public function load(
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?string {
        if (array_key_exists('formType', $actionConfig)) {
            $formTypeClass = $actionConfig['formType'];
        } else {
            $formTypeClass = $this->tryGetFallBackFormTypeClass($entityClass, $action);
        }

        if (null === $formTypeClass) {
            return null;
        }

        Assert::string($formTypeClass);
        Assert::classExists($formTypeClass);
        Assert::subclassOf($formTypeClass, FormInterface::class);

        return $formTypeClass;
    }

    /**
     * @param class-string     $class
     * @param non-empty-string $action
     *
     * @return null|class-string<FormInterface>
     */
    private function tryGetFallBackFormTypeClass(
        string $class,
        string $action,
    ): ?string {
        $classShortName  = u($class)->afterLast('\\')->toString();
        $actionCamelName = u($action)->camel()->title()->toString();

        $candidates = [
            "App\\Form\\{$classShortName}\\{$actionCamelName}Type",
            "App\\Form\\{$classShortName}{$actionCamelName}Type",
        ];

        foreach ($candidates as $candidate) {
            if (class_exists($candidate)) {
                Assert::subclassOf($candidate, FormInterface::class);

                return $candidate;
            }
        }

        return null;
    }
}
