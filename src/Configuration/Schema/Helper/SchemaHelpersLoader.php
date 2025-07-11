<?php

namespace Jmf\CrudEngine\Configuration\Schema\Helper;

use Webmozart\Assert\Assert;

readonly class SchemaHelpersLoader
{
    /**
     * @param array<string, mixed> $schemaConfig
     */
    public function load(array $schemaConfig): SchemaHelpersCollection
    {
        $helperClasses = $schemaConfig['helper'] ?? [];

        if ([] === $helperClasses) {
            $helperClasses = SchemaHelpersCollection::DEFAULT_CLASSES;
        }

        Assert::allStringNotEmpty($helperClasses);

        return new SchemaHelpersCollection(
            $helperClasses,
        );
    }
}
