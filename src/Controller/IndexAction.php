<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Configuration\Action\ActionConfiguration;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\IndexActionHelperInterface;
use Jmf\CrudEngine\Controller\Traits\WithActionHelperTrait;
use Jmf\CrudEngine\Controller\Traits\WithEntityManagerTrait;
use Jmf\CrudEngine\Controller\Traits\WithViewTrait;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineViewRenderingException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * @template E of object
 */
#[AsController]
readonly class IndexAction
{
    /**
     * @use WithActionHelperTrait<IndexActionHelperInterface<E>>
     */
    use WithActionHelperTrait;
    use WithEntityManagerTrait;
    use WithViewTrait;

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
     * @throws CrudEngineViewRenderingException
     */
    public function __invoke(
        Request $request,
        string $entityClass,
    ): Response {
        $actionConfiguration = $this->actionConfigurationRepository->get($entityClass, 'index');

        $actionHelper = $this->getActionHelper(
            IndexActionHelperInterface::class,
            $actionConfiguration,
        );

        $actionHelper->hookBeforeRender($request);

        return $this->render(
            $actionConfiguration,
            $this->getViewContext(
                $request,
                $actionConfiguration,
                $actionHelper,
                [
                    'entities' => $this->getEntities($request, $entityClass, $actionHelper),
                ],
            ),
        );
    }

    /**
     * @param IndexActionHelperInterface<E> $actionHelper
     * @param class-string<E>               $entityClass
     *
     * @return E[]
     */
    private function getEntities(
        Request $request,
        string $entityClass,
        IndexActionHelperInterface $actionHelper,
    ): iterable {
        return $actionHelper->getEntities(
            $request,
            $this->getRepository($entityClass),
        );
    }

    /**
     * @param IndexActionHelperInterface<E> $actionHelper
     * @param array<string, mixed>          $defaults
     *
     * @return array<string, mixed>
     *
     * @throws CrudEngineMissingConfigurationException
     */
    private function getViewContext(
        Request $request,
        ActionConfiguration $actionConfiguration,
        IndexActionHelperInterface $actionHelper,
        array $defaults,
    ): array {
        return array_merge(
            $actionHelper->getViewVariables($request),
            $this->mapViewVariables(
                $actionConfiguration,
                $defaults,
            ),
        );
    }
}
