<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Configuration\Schema\View;

use Jmf\CrudEngine\Configuration\Schema\View\ViewFallbackMode;
use Jmf\CrudEngine\Configuration\Schema\View\ViewSchema;
use Jmf\CrudEngine\Configuration\Schema\View\ViewSchemaLoader;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

final class ViewSchemaLoaderTest extends TestCase
{
    private ViewSchemaLoader $viewSchemaLoader;

    #[Override]
    protected function setUp(): void
    {
        $this->viewSchemaLoader = new ViewSchemaLoader();
    }

    public function testLoadDefaults(): void
    {
        $viewSchema = $this->viewSchemaLoader->load([]);

        self::assertSame(ViewSchema::DEFAULT_PATH, $viewSchema->getPath());
        self::assertSame(ViewFallbackMode::RENDER_BUILT_IN, $viewSchema->getViewFallbackMode());
    }

    public function testLoadFallbackDefaultsToBuiltInWhenViewConfigured(): void
    {
        $viewSchema = $this->viewSchemaLoader->load(
            [
                'view' => [
                    'path' => 'foo/bar.html.twig',
                ],
            ],
        );

        self::assertSame(ViewFallbackMode::RENDER_BUILT_IN, $viewSchema->getViewFallbackMode());
    }

    public function testLoadFallbackError(): void
    {
        $viewSchema = $this->viewSchemaLoader->load(
            [
                'view' => [
                    'fallback' => 'fail',
                ],
            ],
        );

        self::assertSame(ViewFallbackMode::FAIL, $viewSchema->getViewFallbackMode());
    }

    public function testLoadFallbackBuiltIn(): void
    {
        $viewSchema = $this->viewSchemaLoader->load(
            [
                'view' => [
                    'fallback' => 'built_in',
                ],
            ],
        );

        self::assertSame(ViewFallbackMode::RENDER_BUILT_IN, $viewSchema->getViewFallbackMode());
    }

    public function testLoadRejectsUnknownFallback(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->viewSchemaLoader->load(
            [
                'view' => [
                    'fallback' => 'nope',
                ],
            ],
        );
    }
}
