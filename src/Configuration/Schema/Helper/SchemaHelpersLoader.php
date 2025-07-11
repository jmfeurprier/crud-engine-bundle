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
        if (!array_key_exists('helper', $schemaConfig)) {
            return SchemaHelpersCollection::createDefault();
        }

        $helperClasses = $schemaConfig['helper'];

        Assert::allStringNotEmpty($helperClasses);

        return new SchemaHelpersCollection(
            $helperClasses,
        );
    }
}
