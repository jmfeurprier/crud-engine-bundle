<?php

namespace Jmf\CrudEngine\Tests\Configuration\Entities\Action\View;

use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfigurationLoader;
use Jmf\CrudEngine\Configuration\Entities\Action\View\Path\ActionViewPathResolver;
use Jmf\CrudEngine\Configuration\Entities\Action\View\Variables\ActionViewVariablesResolver;
use Jmf\CrudEngine\Configuration\Schema\FormType\FormTypeSchema;
use Jmf\CrudEngine\Configuration\Schema\Helper\HelperSchema;
use Jmf\CrudEngine\Configuration\Schema\Route\Paths\RoutePathSchema;
use Jmf\CrudEngine\Configuration\Schema\Route\RouteSchema;
use Jmf\CrudEngine\Configuration\Schema\Schema;
use Jmf\CrudEngine\Configuration\Schema\View\ViewSchema;
use Jmf\CrudEngine\Configuration\Schema\View\Variables\ViewVariablesSchema;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\TemplateRendering\TemplateRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

class ViewConfigurationLoaderTest extends TestCase
{
    private ActionViewConfigurationLoader $actionViewConfigurationLoader;

    protected function setUp(): void
    {
        $twigEnvironment = new Environment(new ArrayLoader());
        $twigEnvironment->addExtension(new StringExtension());

        $this->actionViewConfigurationLoader = new ActionViewConfigurationLoader(
            new ActionViewVariablesResolver(
                new SchemaValueExpander(new TemplateRenderer($twigEnvironment)),
            ),
            new ActionViewPathResolver(
                new SchemaValueExpander(new TemplateRenderer($twigEnvironment)),
            ),
        );
    }

    /**
     * @return array{
     *     0: non-empty-string,
     *     1: non-empty-string,
     *     2: array<string, mixed>,
     *     3: non-empty-string,
     *     4: array<string, string>,
     * }[]
     */
    public static function dataProviderClassActionAndViewPath(): iterable
    {
        return [
            [
                'App\\Entity\\Article',
                'create',
                [],
                'article/create.html.twig',
                [],
            ],
            [
                'App\\Entity\\Article',
                'create',
                [
                    'view' => [],
                ],
                'article/create.html.twig',
                [],
            ],
            [
                'App\\Entity\\Article',
                'create',
                [
                    'view' => [
                        'path' => 'foo/bar.baz',
                    ],
                ],
                'foo/bar.baz',
                [],
            ],
        ];
    }

    /**
     * @param class-string          $entityClass
     * @param non-empty-string      $action
     * @param array<string, mixed>  $actionConfig
     * @param class-string          $viewPath
     * @param array<string, string> $viewVariables
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    #[DataProvider('dataProviderClassActionAndViewPath')]
    public function testLoad(
        string $entityClass,
        string $action,
        array $actionConfig,
        string $viewPath,
        array $viewVariables,
    ): void {
        $schema = new Schema(
            $this->createMock(FormTypeSchema::class),
            $this->createMock(HelperSchema::class),
            new RouteSchema(
                RouteSchema::DEFAULT_NAME,
                RoutePathSchema::createDefault(),
            ),
            new ViewSchema(
                ViewSchema::DEFAULT_PATH,
                ViewVariablesSchema::createDefault(),
            ),
        );

        $result = $this->actionViewConfigurationLoader->load(
            $schema,
            $entityClass,
            $action,
            $actionConfig,
        );

        self::assertSame($viewPath, $result->getPath());
        self::assertSame($viewVariables, $result->getVariables()->all());
    }
}
