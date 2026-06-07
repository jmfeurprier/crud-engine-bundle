<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Model;

/**
 * The five CRUD actions, and the single neutral source of their canonical names. Shared by the
 * controllers, the route loaders, and the per-action config resolvers so none has to depend on the
 * others for the action identity.
 */
enum CrudAction: string
{
    case Index  = 'index';
    case Read   = 'read';
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
}
