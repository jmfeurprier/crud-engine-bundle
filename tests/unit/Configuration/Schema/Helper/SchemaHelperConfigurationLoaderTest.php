<?php

namespace Jmf\CrudEngine\Tests\Configuration\Schema\Helper;

use Jmf\CrudEngine\Configuration\Schema\Helper\SchemaHelpersCollection;
use Jmf\CrudEngine\Configuration\Schema\Helper\SchemaHelpersLoader;
use Override;
use PHPUnit\Framework\TestCase;

class SchemaHelperConfigurationLoaderTest extends TestCase
{
    private SchemaHelpersLoader $schemaHelperConfigurationLoader;

    #[Override]
    protected function setUp(): void
    {
        $this->schemaHelperConfigurationLoader = new SchemaHelpersLoader();
    }

    public function testLoadDefault(): void
    {
        $schemaConfig = [];

        $result = $this->schemaHelperConfigurationLoader->load($schemaConfig);

        self::assertSame(SchemaHelpersCollection::DEFAULT_CLASSES, $result->all());
    }

    public function testLoadWithEmptyConfig(): void
    {
        $schemaConfig = [
            'helper' => [],
        ];

        $result = $this->schemaHelperConfigurationLoader->load($schemaConfig);

        self::assertSame(SchemaHelpersCollection::DEFAULT_CLASSES, $result->all());
    }

    public function testLoad(): void
    {
        $schemaConfig = [
            'helper' => [
                'foo',
                'bar',
            ],
        ];

        $result = $this->schemaHelperConfigurationLoader->load($schemaConfig);

        self::assertSame(
            [
                'foo',
                'bar',
            ],
            $result->all(),
        );
    }
}
