<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\Helper;

use Jmf\CrudEngine\Configuration\Schema\Helper\HelperSchema;
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
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $actionConfig
     *
     * @return null|class-string
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function resolve(
        HelperSchema $helperSchema,
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): ?string {
        if (!array_key_exists('helper', $actionConfig)) {
            return $this->tryGetFallBackHelperClass(
                $helperSchema,
                $keys,
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
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     *
     * @return null|class-string
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function tryGetFallBackHelperClass(
        HelperSchema $helperSchema,
        array $keys,
        string $entityClass,
        string $action,
    ): ?string {
        $helperClasses = $helperSchema->expand(
            $this->schemaValueExpander,
            array_merge(
                $keys,
                [
                    'entityClass' => $entityClass,
                    'action'      => $action,
                ],
            ),
        );

        foreach ($helperClasses as $helperClass) {
            if (class_exists($helperClass)) {
                // @todo Validate "subclass of".
                return $helperClass;
            }
        }

        return null;
    }
}
