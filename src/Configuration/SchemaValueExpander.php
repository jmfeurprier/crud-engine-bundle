<?php

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Throwable;

readonly class SchemaValueExpander
{
    public function __construct(
        private TemplateRendererInterface $templateRenderer,
    ) {
    }

    /**
     * @param array<string, mixed> $arguments
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function expand(
        string $value,
        array $arguments,
    ): string {
        try {
            return $this->templateRenderer->renderFromString(
                $value,
                $arguments,
            );
        } catch (Throwable $e) {
            throw new CrudEngineInvalidConfigurationException(
                message:  'Failed expanding configuration value.',
                previous: $e,
            );
        }
    }
}
