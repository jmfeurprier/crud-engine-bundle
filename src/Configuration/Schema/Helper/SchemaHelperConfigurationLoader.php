<?php

namespace Jmf\CrudEngine\Configuration\Schema\Helper;

use Webmozart\Assert\Assert;

readonly class SchemaHelperConfigurationLoader
{
    /**
     * @param array<string, mixed> $schemaConfig
     */
    public function load(array $schemaConfig): SchemaHelperConfiguration
    {
        $helperClasses = $schemaConfig['helper'] ?? [];

        if ([] === $helperClasses) {
            $helperClasses = SchemaHelperConfiguration::DEFAULT_CLASSES;
        }

        Assert::allStringNotEmpty($helperClasses);

        return new SchemaHelperConfiguration(
            $helperClasses,
        );
    }
}
