<?php

namespace Jmf\CrudEngine\Configuration\Schema\Keys;

use Webmozart\Assert\Assert;

readonly class SchemaKeysLoader
{
    /**
     * @param array<string, mixed> $actionConfig
     */
    public function load(
        array $actionConfig,
    ): SchemaKeysCollection {
        if (!array_key_exists('keys', $actionConfig)) {
            return SchemaKeysCollection::createDefault();
        }

        $keysConfig = $actionConfig['keys'];

        Assert::isMap($keysConfig);

        $keys = [];

        foreach ($keysConfig as $key => $value) {
            Assert::stringNotEmpty($key);
            Assert::stringNotEmpty($value);

            $keys[$key] = $value;
        }

        return new SchemaKeysCollection($keys);
    }
}
