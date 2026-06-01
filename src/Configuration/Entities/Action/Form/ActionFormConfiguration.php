<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\Form;

use Symfony\Component\Form\FormTypeInterface;

readonly class ActionFormConfiguration
{
    /**
     * @param null|class-string<FormTypeInterface> $formTypeClass
     */
    public function __construct(
        private ?string $formTypeClass,
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

    public function getFormFallbackMode(): FormFallbackMode
    {
        return $this->formFallbackMode;
    }
}
