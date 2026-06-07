<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Form;

enum FormFallbackMode: string
{
    case FAIL    = 'fail';
    case PROVIDE = 'provide';
}
