<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\Form;

enum FormFallbackMode: string
{
    /**
     * Provide a generic form from the entity's Doctrine metadata when no form type is found.
     */
    case PROVIDE = 'provide';

    /**
     * Throw an exception when no form type is found.
     */
    case FAIL = 'fail';
}
