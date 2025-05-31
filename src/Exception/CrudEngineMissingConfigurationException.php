<?php

namespace Jmf\CrudEngine\Exception;

class CrudEngineMissingConfigurationException extends CrudEngineException
{
    public function __construct(
        private readonly string $entityClass,
        private readonly string $action,
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
        if (null === $this->configurationKey) {
            return 'Missing configuration for entity class %s and action "%s".';
        }

        return 'Missing configuration "%s" for entity class %s and action "%s".';
    }

    /**
     * @return string[]
     */
    private function getVars(): array
    {
        if (null === $this->configurationKey) {
            return [
                $this->entityClass,
                $this->action,
            ];
        }

        return [
            $this->entityClass,
            $this->action,
            $this->configurationKey,
        ];
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getConfigurationKey(): ?string
    {
        return $this->configurationKey;
    }
}
