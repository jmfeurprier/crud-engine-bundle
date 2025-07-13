<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Schema\Helper;

use Webmozart\Assert\Assert;

readonly class HelperSchemaLoader
{
    /**
     * @param array<string, mixed> $schemaConfig
     */
    public function load(array $schemaConfig): HelperSchema
    {
        if (!array_key_exists('helper', $schemaConfig)) {
            return HelperSchema::createDefault();
        }

        $helperClasses = $schemaConfig['helper'];

        Assert::allStringNotEmpty($helperClasses);

        return new HelperSchema(
            $helperClasses,
        );
    }
}
