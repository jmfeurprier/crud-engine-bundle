<?php

namespace Jmf\CrudEngine\Configuration\Action\FormType;

use Jmf\CrudEngine\Configuration\EntityConfigurationFallbacksResolver;
use Webmozart\Assert\Assert;

readonly class FormTypeClassConfigurationLoader
{
    public function __construct(
        private EntityConfigurationFallbacksResolver $fallbacksResolver,
    ) {
    }

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
        if (!array_key_exists('formType', $actionConfig)) {
            return $this->fallbacksResolver->tryResolveFormTypeClass($entityClass, $action);
        }

        if (null === $actionConfig['formType']) {
            return null;
        }

        Assert::string($actionConfig['formType']);
        Assert::classExists($actionConfig['formType']);

        return $actionConfig['formType'];
    }
}
