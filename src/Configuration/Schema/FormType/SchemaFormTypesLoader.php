<?php

namespace Jmf\CrudEngine\Configuration\Schema\FormType;

use Webmozart\Assert\Assert;

readonly class SchemaFormTypesLoader
{
    /**
     * @param array<non-empty-string, mixed> $schemaConfig
     */
    public function load(array $schemaConfig): SchemaFormTypesCollection
    {
        if (!array_key_exists('formTypes', $schemaConfig)) {
            return SchemaFormTypesCollection::createDefault();
        }

        $formTypeClasses = $schemaConfig['formType'];

        Assert::allStringNotEmpty($formTypeClasses);

        return new SchemaFormTypesCollection(
            $formTypeClasses,
        );
    }
}
