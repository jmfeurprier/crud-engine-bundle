<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\IndexActionHelperInterface;
use Jmf\CrudEngine\Controller\Traits\WithActionHelperTrait;
use Jmf\CrudEngine\Controller\Traits\WithEntityManagerTrait;
use Jmf\CrudEngine\Controller\Traits\WithViewTrait;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\TemplateRendering\Exception\TemplateRenderingException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * @template E of object
 */
#[AsController]
class IndexAction
{
    /**
     * @use WithActionHelperTrait<IndexActionHelperInterface<E>>
     */
    use WithActionHelperTrait;
    use WithEntityManagerTrait;
    use WithViewTrait;

    private Request $request;

    /**
     * @var IndexActionHelperInterface<E>
     */
    private IndexActionHelperInterface $actionHelper;

    /**
     * @psalm-param IndexActionHelperInterface<E> $defaultActionHelper
     */
    public function __construct(
        TemplateRendererInterface $templateRenderer,
        ManagerRegistry $managerRegistry,
        IndexActionHelperInterface $defaultActionHelper,
        ActionHelperResolver $actionHelperResolver,
        private readonly ActionConfigurationRepositoryInterface $actionConfigurationRepository,
    ) {
        $this->templateRenderer     = $templateRenderer;
        $this->managerRegistry      = $managerRegistry;
        $this->defaultActionHelper  = $defaultActionHelper;
        $this->actionHelperResolver = $actionHelperResolver;
    }

    /**
     * @param class-string<E> $entityClass
     *
     * @throws CrudEngineInvalidActionHelperException
     * @throws CrudEngineMissingConfigurationException
     * @throws TemplateRenderingException
     */
    public function __invoke(
        Request $request,
        string $entityClass,
    ): Response {
        $actionConfiguration = $this->actionConfigurationRepository->get($entityClass, 'index');
        $this->request       = $request;
        $this->actionHelper  = $this->getActionHelper(
            IndexActionHelperInterface::class,
            $actionConfiguration,
        );

        $this->hookBeforeRender($this->actionHelper, $request);

        return $this->render(
            $actionConfiguration,
            $this->getViewContext(
                $actionConfiguration,
                [
                    'entities' => $this->getEntities($this->actionHelper, $entityClass),
                ],
            ),
        );
    }

    /**
     * @param IndexActionHelperInterface<E> $actionHelper
     */
    private function hookBeforeRender(
        IndexActionHelperInterface $actionHelper,
        Request $request,
    ): void {
        $actionHelper->hookBeforeRender($request);
    }

    /**
     * @param IndexActionHelperInterface<E> $actionHelper
     * @param class-string<E>               $entityClass
     *
     * @return E[]
     */
    private function getEntities(
        IndexActionHelperInterface $actionHelper,
        string $entityClass,
    ): iterable {
        return $actionHelper->getEntities(
            $this->getRepository($entityClass),
        );
    }

    /**
     * @param array<string, mixed> $defaults
     *
     * @return array<string, mixed>
     *
     * @throws CrudEngineMissingConfigurationException
     */
    private function getViewContext(
        ActionConfiguration $actionConfiguration,
        array $defaults,
    ): array {
        return array_merge(
            $this->actionHelper->getViewVariables($this->request),
            $this->mapViewVariables(
                $actionConfiguration,
                $defaults,
            ),
        );
    }
}
