<?php

namespace Jmf\CrudEngine\Configuration\Action\Helper;

use Jmf\CrudEngine\Configuration\EntityConfigurationFallbacksResolver;
use Webmozart\Assert\Assert;

readonly class HelperClassConfigurationLoader
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
        if (!array_key_exists('helper', $actionConfig)) {
            return $this->fallbacksResolver->tryResolveHelperClass($entityClass, $action);
        }

        if (null === $actionConfig['helper']) {
            return null;
        }

        Assert::string($actionConfig['helper']);
        Assert::classExists($actionConfig['helper']);

        return $actionConfig['helper'];
    }
}
