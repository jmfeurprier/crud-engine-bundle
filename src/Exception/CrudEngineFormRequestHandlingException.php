<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Exception;

use Jmf\CrudEngine\Model\EntityAction;
use Throwable;

final class CrudEngineFormRequestHandlingException extends CrudEngineRuntimeException
{
    public function __construct(
        private readonly EntityAction $entityAction,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message:  sprintf(
                          'Failed handling the form request for entity "%s" for action "%s".',
                          $entityAction->getEntityClass(),
                          $entityAction->getAction(),
                      ),
            previous: $previous,
        );
    }

    public function getEntityAction(): EntityAction
    {
        return $this->entityAction;
    }
}
