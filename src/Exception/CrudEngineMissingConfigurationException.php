<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Model\CrudAction;

class CrudEngineMissingConfigurationException extends CrudEngineConfigurationException
{
    public function __construct(
        private readonly string $entityClass,
        private readonly ?CrudAction $action = null,
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

        if ($this->action instanceof CrudAction) {
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

        if ($this->action instanceof CrudAction) {
            $vars[] = $this->action->value;
        }

        return $vars;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getAction(): ?CrudAction
    {
        return $this->action;
    }

    public function getConfigurationKey(): ?string
    {
        return $this->configurationKey;
    }
}
