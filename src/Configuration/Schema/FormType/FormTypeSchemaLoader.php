<?php

namespace Jmf\CrudEngine\Configuration\Schema\FormType;

use Webmozart\Assert\Assert;

readonly class FormTypeSchemaLoader
{
    /**
     * @param array<string, mixed> $schemaConfig
     */
    public function load(array $schemaConfig): FormTypeSchema
    {
        if (!array_key_exists('formTypes', $schemaConfig)) {
            return FormTypeSchema::createDefault();
        }

        $formTypeClasses = $schemaConfig['formType'];

        Assert::allStringNotEmpty($formTypeClasses);

        return new FormTypeSchema(
            $formTypeClasses,
        );
    }
}
