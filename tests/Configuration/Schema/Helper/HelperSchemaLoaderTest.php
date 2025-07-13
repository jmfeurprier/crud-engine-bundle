<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Configuration\Schema\Helper;

use Jmf\CrudEngine\Configuration\Schema\Helper\HelperSchemaLoader;
use Override;
use PHPUnit\Framework\TestCase;

final class HelperSchemaLoaderTest extends TestCase
{
    private HelperSchemaLoader $helperSchemaLoader;

    #[Override]
    protected function setUp(): void
    {
        $this->helperSchemaLoader = new HelperSchemaLoader();
    }

    public function testLoadDefault(): void
    {
        $schemaConfig = [];

        $helperSchema = $this->helperSchemaLoader->load($schemaConfig);

        $this->assertNotEmpty($helperSchema->all());
    }

    public function testLoadWithEmptyConfig(): void
    {
        $schemaConfig = [
            'helper' => [],
        ];

        $helperSchema = $this->helperSchemaLoader->load($schemaConfig);

        $this->assertSame([], $helperSchema->all());
    }

    public function testLoad(): void
    {
        $schemaConfig = [
            'helper' => [
                'foo',
                'bar',
            ],
        ];

        $helperSchema = $this->helperSchemaLoader->load($schemaConfig);

        $this->assertSame(
            [
                'foo',
                'bar',
            ],
            $helperSchema->all(),
        );
    }
}
