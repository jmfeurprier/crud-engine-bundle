<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Action\View;

enum ViewFallbackMode: string
{
    case FAIL    = 'fail';
    case PROVIDE = 'provide';
}
