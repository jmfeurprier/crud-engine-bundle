<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

class CrudEngineDuplicateEntityException extends CrudEngineConfigurationException
{
    /**
     * @param non-empty-list<string> $entityClasses
     */
    public function __construct(
        private readonly array $entityClasses,
    ) {
        parent::__construct(
            sprintf(
                'Duplicate CRUD entity configuration for %s: defined both inline under "entities" and via "paths".',
                implode(', ', $this->entityClasses),
            ),
        );
    }

    /**
     * @return non-empty-list<string>
     */
    public function getEntityClasses(): array
    {
        return $this->entityClasses;
    }
}
