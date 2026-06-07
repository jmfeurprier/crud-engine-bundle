<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Definition;

enum FallbackMode: string
{
    case FAIL    = 'fail';
    case PROVIDE = 'provide';
}
