<?php

namespace Jmf\CrudEngine\Tests\Configuration\Schema\Helper;

use Jmf\CrudEngine\Configuration\Schema\Helper\SchemaHelperConfiguration;
use Jmf\CrudEngine\Configuration\Schema\Helper\SchemaHelperConfigurationLoader;
use Override;
use PHPUnit\Framework\TestCase;

class SchemaHelperConfigurationLoaderTest extends TestCase
{
    private SchemaHelperConfigurationLoader $schemaHelperConfigurationLoader;

    #[Override]
    protected function setUp(): void
    {
        $this->schemaHelperConfigurationLoader = new SchemaHelperConfigurationLoader();
    }

    public function testLoadDefault(): void
    {
        $schemaConfig = [];

        $result = $this->schemaHelperConfigurationLoader->load($schemaConfig);

        self::assertSame(SchemaHelperConfiguration::DEFAULT_CLASSES, $result->getClasses());
    }

    public function testLoadWithEmptyConfig(): void
    {
        $schemaConfig = [
            'helper' => [],
        ];

        $result = $this->schemaHelperConfigurationLoader->load($schemaConfig);

        self::assertSame(SchemaHelperConfiguration::DEFAULT_CLASSES, $result->getClasses());
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
            $result->getClasses(),
        );
    }
}
