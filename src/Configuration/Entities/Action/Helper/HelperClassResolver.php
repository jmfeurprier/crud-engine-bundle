<?php

namespace Jmf\CrudEngine\Configuration\Entities\Action\Helper;

use Jmf\CrudEngine\Configuration\Schema\SchemaConfiguration;
use Webmozart\Assert\Assert;
use function Symfony\Component\String\u;

readonly class HelperClassResolver
{
    /**
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @return null|class-string
     */
    public function resolve(
        SchemaConfiguration $schemaConfiguration,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?string {
        if (!array_key_exists('helper', $actionConfig)) {
            return $this->tryGetFallBackHelperClass($entityClass, $action);
        }

        $helperClass = $actionConfig['helper'];

        if (null === $helperClass) {
            return null;
        }

        Assert::string($helperClass);
        Assert::classExists($helperClass);

        return $helperClass;
    }

    /**
     * @param class-string     $class
     * @param non-empty-string $action
     *
     * @return null|class-string
     */
    private function tryGetFallBackHelperClass(
        string $class,
        string $action,
    ): ?string {
        $classShortName  = u($class)->afterLast('\\')->toString();
        $actionCamelName = u($action)->camel()->title()->toString();

        $candidates = [
            "App\\Controller\\{$classShortName}\\{$actionCamelName}ActionHelper",
            "App\\Controller\\{$classShortName}{$actionCamelName}ActionHelper",
        ];

        foreach ($candidates as $candidate) {
            if (class_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
