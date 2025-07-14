<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Tests\Configuration\Entities\Action\View;

use Jmf\CrudEngine\Configuration\Entities\Action\View\ActionViewConfigurationLoader;
use Jmf\CrudEngine\Configuration\Entities\Action\View\Path\ActionViewPathResolver;
use Jmf\CrudEngine\Configuration\Entities\Action\View\Variables\ActionViewVariablesResolver;
use Jmf\CrudEngine\Configuration\Schema\View\Variables\ViewVariablesSchema;
use Jmf\CrudEngine\Configuration\Schema\View\ViewSchema;
use Jmf\CrudEngine\Configuration\SchemaValueExpander;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\TemplateRendering\TemplateRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\ArrayLoader;

final class ActionViewConfigurationLoaderTest extends TestCase
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
     *     0: array<non-empty-string, non-empty-string>,
     *     1: non-empty-string,
     *     2: non-empty-string,
     *     3: array<string, mixed>,
     *     4: non-empty-string,
     *     5: array<string, string>,
     * }[]
     */
    public static function dataProviderClassActionAndViewPath(): iterable
    {
        return [
            [
                [
                    'action_key' => 'create',
                    'entity_key' => 'article',
                ],
                'App\\Entity\\Article',
                'create',
                [],
                'article/create.html.twig',
                [],
            ],
            [
                [
                    'action_key' => 'create',
                    'entity_key' => 'article',
                ],
                'App\\Entity\\Article',
                'create',
                [
                    'view' => [],
                ],
                'article/create.html.twig',
                [],
            ],
            [
                [
                    'action_key' => 'create',
                    'entity_key' => 'article',
                ],
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
     * @param array<non-empty-string, non-empty-string> $keys
     * @param class-string                              $entityClass
     * @param non-empty-string                          $action
     * @param array<string, mixed>                      $actionConfig
     * @param class-string                              $viewPath
     * @param array<string, string>                     $viewVariables
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    #[DataProvider('dataProviderClassActionAndViewPath')]
    public function testLoad(
        array $keys,
        string $entityClass,
        string $action,
        array $actionConfig,
        string $viewPath,
        array $viewVariables,
    ): void {
        $viewSchema = new ViewSchema(
            ViewSchema::DEFAULT_PATH,
            ViewVariablesSchema::createDefault(),
        );

        $actionViewConfiguration = $this->actionViewConfigurationLoader->load(
            $viewSchema,
            $keys,
            $entityClass,
            $action,
            $actionConfig,
        );

        $this->assertSame($viewPath, $actionViewConfiguration->getPath());
        $this->assertSame($viewVariables, $actionViewConfiguration->getVariables()->all());
    }
}
