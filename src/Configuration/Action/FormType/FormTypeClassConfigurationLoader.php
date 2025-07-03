<?php

namespace Jmf\CrudEngine\Configuration\Action\FormType;

use Webmozart\Assert\Assert;
use function Symfony\Component\String\u;

readonly class FormTypeClassConfigurationLoader
{
    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @return null|class-string
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

        return $formTypeClass;
    }

    /**
     * @param class-string     $class
     * @param non-empty-string $action
     *
     * @return null|class-string
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
                return $candidate;
            }
        }

        return null;
    }
}
