<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\Form;

enum FormFallbackMode: string
{
    /**
     * Build a generic form from the entity's Doctrine metadata when no form type is found.
     */
    case GENERIC = 'generic';

    /**
     * Throw an exception when no form type is found.
     */
    case FAIL = 'fail';
}
