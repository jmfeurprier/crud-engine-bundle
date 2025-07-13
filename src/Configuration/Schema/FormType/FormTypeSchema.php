<?php

namespace Jmf\CrudEngine\Configuration\Schema\FormType;

use Jmf\CrudEngine\Configuration\Schema\Schema;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Webmozart\Assert\Assert;

readonly class FormTypeSchema
{
    /**
     * @const non-empty-string[]
     */
    private const iterable DEFAULT_FORM_TYPES = [
        "App\\Form\\{{ entityClass|u.afterLast('\\\\') }}\\{{ action|u.title }}Type",
        "App\\Form\\{{ entityClass|u.afterLast('\\\\') }}{{ action|u.title }}Type",
        "App\\Form\\{{ entityClass|u.afterLast('\\\\') }}Type",
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
     * @return non-empty-string[]
     */
    public function all(): iterable
    {
        return $this->formTypes;
    }

    /**
     * @param array<non-empty-string, mixed> $arguments
     *
     * @return non-empty-string[]
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function expand(
        SchemaValueExpander $schemaValueExpander,
        array $arguments,
    ): iterable {
        $expanded = array_map(
            static fn(
                $value,
            ): string => $schemaValueExpander->expand($value, $arguments),
            (array) $this->formTypes,
        );

        Assert::allStringNotEmpty($expanded);

        return $expanded;
    }
}
