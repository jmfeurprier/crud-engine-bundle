<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\Form;

use Symfony\Component\Form\FormTypeInterface;

readonly class ActionFormConfiguration
{
    /**
     * @param null|class-string<FormTypeInterface> $formTypeClass
     * @param non-empty-string                     $suggestedFormTypeClass
     */
    public function __construct(
        private ?string $formTypeClass,
        private string $suggestedFormTypeClass,
        private FormFallbackMode $formFallbackMode,
    ) {
    }

    /**
     * @return null|class-string<FormTypeInterface>
     */
    public function getFormTypeClass(): ?string
    {
        return $this->formTypeClass;
    }

    /**
     * Conventional class to create in order to customize this form (it may not exist yet).
     *
     * @return non-empty-string
     */
    public function getSuggestedFormTypeClass(): string
    {
        return $this->suggestedFormTypeClass;
    }

    public function getFormFallbackMode(): FormFallbackMode
    {
        return $this->formFallbackMode;
    }
}
