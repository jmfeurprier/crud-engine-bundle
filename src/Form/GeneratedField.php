<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Form;

use Symfony\Component\Form\FormTypeInterface;

readonly class GeneratedField
{
    /**
     * @param class-string<FormTypeInterface> $typeClass
     * @param array<string, mixed>            $options
     */
    public function __construct(
        private string $typeClass,
        private array $options = [],
    ) {
    }

    /**
     * @return class-string<FormTypeInterface>
     */
    public function getTypeClass(): string
    {
        return $this->typeClass;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
