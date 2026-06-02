<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Entities\Action\View;

enum ViewFallbackMode: string
{
    /**
     * Render a built-in bare template when the configured view is missing.
     */
    case BUILT_IN = 'built_in';

    /**
     * Throw an exception when the configured view is missing.
     */
    case FAIL = 'fail';
}
