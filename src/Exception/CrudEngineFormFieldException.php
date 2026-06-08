<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Model\EntityAction;
use Throwable;

final class CrudEngineFormFieldException extends CrudEngineRuntimeException
{
    public function __construct(
        private readonly EntityAction $entityAction,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message:  sprintf(
                          'Failed building a form field for entity "%s" for action "%s".',
                          $entityAction->getEntityClass(),
                          $entityAction->getAction()->value,
                      ),
            previous: $previous,
        );
    }

    public function getEntityAction(): EntityAction
    {
        return $this->entityAction;
    }
}
