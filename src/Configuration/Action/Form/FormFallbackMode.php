<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Action\Form;

enum FormFallbackMode: string
{
    case FAIL    = 'fail';
    case PROVIDE = 'provide';
}
