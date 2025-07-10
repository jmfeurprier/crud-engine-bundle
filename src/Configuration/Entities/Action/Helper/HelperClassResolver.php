<?php

namespace Jmf\CrudEngine\Configuration\Entities\Action\Helper;

use Jmf\CrudEngine\Configuration\Schema\SchemaConfiguration;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Webmozart\Assert\Assert;

readonly class HelperClassResolver
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
     * @return null|class-string
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function resolve(
        SchemaConfiguration $schemaConfiguration,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?string {
        if (!array_key_exists('helper', $actionConfig)) {
            return $this->tryGetFallBackHelperClass(
                $schemaConfiguration,
                $entityClass,
                $action,
            );
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
     * @param class-string     $entityClass
     * @param non-empty-string $action
     *
     * @return null|class-string
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function tryGetFallBackHelperClass(
        SchemaConfiguration $schemaConfiguration,
        string $entityClass,
        string $action,
    ): ?string {
        foreach ($schemaConfiguration->getHelperConfiguration()->getClasses() as $schemaClass) {
            $class = $this->schemaValueExpander->expand(
                $schemaClass,
                [
                    'entityClass' => $entityClass,
                    'action'      => $action,
                ],
            );

            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }
}
