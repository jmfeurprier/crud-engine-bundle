<?php

namespace Jmf\CrudEngine\Configuration;

use function Symfony\Component\String\u;

readonly class ActionConfigurationFallbacksResolver
{
    public function __construct(
        private string $baseNamespace = 'App',
    ) {
    }

    /**
     * @param class-string     $class
     * @param non-empty-string $action
     *
     * @return null|class-string
     */
    public function tryResolveHelperClass(
        string $class,
        string $action,
    ): ?string {
        $classShortName  = $this->getClassShortName($class);
        $actionCamelName = $this->getActionCamelName($action);

        $candidates = [
            "{$this->baseNamespace}\\Controller\\{$classShortName}\\{$actionCamelName}ActionHelper",
            "{$this->baseNamespace}\\Controller\\{$classShortName}{$actionCamelName}ActionHelper",
        ];

        foreach ($candidates as $candidate) {
            if (class_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param class-string     $class
     * @param non-empty-string $action
     *
     * @return null|class-string
     */
    public function tryResolveFormTypeClass(
        string $class,
        string $action,
    ): ?string {
        $classShortName  = $this->getClassShortName($class);
        $actionCamelName = $this->getActionCamelName($action);

        $candidates = [
            "{$this->baseNamespace}\\Form\\{$classShortName}\\{$actionCamelName}Type",
            "{$this->baseNamespace}\\Form\\{$classShortName}{$actionCamelName}Type",
        ];

        foreach ($candidates as $candidate) {
            if (class_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param class-string $class
     */
    private function getClassShortName(string $class): string
    {
        return u($class)->afterLast('\\')->toString();
    }

    /**
     * @param non-empty-string $action
     */
    private function getActionCamelName(string $action): string
    {
        return u($action)->camel()->title()->toString();
    }
}
