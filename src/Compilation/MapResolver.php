<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation;

use Webmozart\Assert\Assert;

readonly class MapResolver
{
    /**
     * @param array<string, mixed> $config
     * @param non-empty-string     $key
     *
     * @return array<string, mixed>
     */
    public function resolve(
        array $config,
        string $key,
    ): array {
        if (!array_key_exists($key, $config)) {
            return [];
        }

        $value = $config[$key];
        Assert::isMap($value);

        return $value;
    }
}
