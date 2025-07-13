<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

class CrudEngineMissingConfigurationException extends CrudEngineConfigurationException
{
    public function __construct(
        private readonly string $entityClass,
        private readonly ?string $action = null,
        private readonly ?string $configurationKey = null,
    ) {
        parent::__construct(
            vsprintf(
                $this->getTemplate(),
                $this->getVars(),
            ),
        );
    }

    private function getTemplate(): string
    {
        $tokens = [
            'Missing configuration',
        ];

        if (null !== $this->configurationKey) {
            $tokens[] = '"%s"';
        }

        $tokens[] = 'for entity class %s';

        if (null !== $this->action) {
            $tokens[] = 'and action "%s"';
        }

        return implode(' ', $tokens) . '.';
    }

    /**
     * @return string[]
     */
    private function getVars(): array
    {
        $vars = [];

        if (null !== $this->configurationKey) {
            $vars[] = $this->configurationKey;
        }

        $vars[] = $this->entityClass;

        if (null !== $this->action) {
            $vars[] = $this->action;
        }

        return $vars;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function getConfigurationKey(): ?string
    {
        return $this->configurationKey;
    }
}
