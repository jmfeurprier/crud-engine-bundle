<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Schema\FormType;

use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Webmozart\Assert\Assert;

readonly class FormTypeSchema
{
    /**
     * @const non-empty-string[]
     */
    private const iterable DEFAULT_FORM_TYPES = [
        "App\\Form\\{{ EntityKey }}\\{{ ActionKey }}Type",
        "App\\Form\\{{ EntityKey }}{{ ActionKey }}Type",
        "App\\Form\\{{ EntityKey }}Type",
    ];

    public static function createDefault(): self
    {
        return new self(self::DEFAULT_FORM_TYPES);
    }

    /**
     * @param non-empty-string[] $formTypes
     */
    public function __construct(
        private iterable $formTypes,
    ) {
        Assert::allStringNotEmpty($formTypes);
    }

    /**
     * @param array<non-empty-string, non-empty-string> $keys
     * @param array<non-empty-string, mixed>            $arguments
     *
     * @return non-empty-string[]
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function expand(
        SchemaValueExpander $schemaValueExpander,
        array $keys,
        array $arguments,
    ): iterable {
        $expanded = array_map(
            static fn(
                $value,
            ): string => $schemaValueExpander->expand($value, $keys, $arguments),
            (array) $this->formTypes,
        );

        Assert::allStringNotEmpty($expanded);

        return $expanded;
    }
}
