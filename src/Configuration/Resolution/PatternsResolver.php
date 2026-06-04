<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Resolution;

use Webmozart\Assert\Assert;

readonly class PatternsResolver
{
    /**
     * @param array<string, mixed> $config
     * @param non-empty-string $key
     * @param list<non-empty-string> $default
     *
     * @return list<non-empty-string>
     */
    public function resolve(
        array $config,
        string $key,
        array $default,
    ): array {
        if (!array_key_exists($key, $config)) {
            return $default;
        }

        $patterns = $config[$key];
        Assert::isArray($patterns);

        if ([] === $patterns) {
            return $default;
        }

        Assert::allStringNotEmpty($patterns);

        return array_values($patterns);
    }
}
