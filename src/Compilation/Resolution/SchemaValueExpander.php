<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation\Resolution;

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
     * @param array<non-empty-string, non-empty-string> $keys
     * @param array<non-empty-string, mixed>            $arguments
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    public function expand(
        string $value,
        array $keys,
        array $arguments,
    ): string {
        try {
            return $this->templateRenderer->renderFromString(
                $value,
                array_merge(
                    $keys,
                    $arguments,
                ),
            );
        } catch (Throwable $e) {
            throw new CrudEngineInvalidConfigurationException(
                message:  sprintf('Failed expanding configuration value "%s".', $value),
                code:     $e->getCode(),
                previous: $e,
            );
        }
    }
}
