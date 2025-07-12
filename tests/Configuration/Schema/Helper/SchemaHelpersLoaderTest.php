<?php

namespace Jmf\CrudEngine\Tests\Configuration\Schema\Helper;

use Jmf\CrudEngine\Configuration\Schema\Helper\HelperSchemaLoader;
use Override;
use PHPUnit\Framework\TestCase;

class SchemaHelpersLoaderTest extends TestCase
{
    private HelperSchemaLoader $schemaHelpersLoader;

    #[Override]
    protected function setUp(): void
    {
        $this->schemaHelpersLoader = new HelperSchemaLoader();
    }

    public function testLoadDefault(): void
    {
        $schemaConfig = [];

        $result = $this->schemaHelpersLoader->load($schemaConfig);

        self::assertNotEmpty($result->all());
    }

    public function testLoadWithEmptyConfig(): void
    {
        $schemaConfig = [
            'helper' => [],
        ];

        $result = $this->schemaHelpersLoader->load($schemaConfig);

        self::assertSame([], $result->all());
    }

    public function testLoad(): void
    {
        $schemaConfig = [
            'helper' => [
                'foo',
                'bar',
            ],
        ];

        $result = $this->schemaHelpersLoader->load($schemaConfig);

        self::assertSame(
            [
                'foo',
                'bar',
            ],
            $result->all(),
        );
    }
}
