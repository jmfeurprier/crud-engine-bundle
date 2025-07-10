<?php

namespace Jmf\CrudEngine\Configuration\Schema\Helper;

use Webmozart\Assert\Assert;

readonly class SchemaHelperConfiguration
{
    /**
     * @const string[]
     */
    public const iterable DEFAULT_CLASSES = [
        "App\\Controller\\{{ entityClass|u.afterLast('\\\\') }}\\{{ action|u.title }}ActionHelper",
        "App\\Controller\\{{ entityClass|u.afterLast('\\\\') }}{{ action|u.title }}ActionHelper",
    ];

    /**
     * @param non-empty-string[] $classes
     */
    public function __construct(
        private iterable $classes,
    ) {
        Assert::allStringNotEmpty($classes);
    }

    /**
     * @return string[]
     */
    public function getClasses(): iterable
    {
        return $this->classes;
    }
}
