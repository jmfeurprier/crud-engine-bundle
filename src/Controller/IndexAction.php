<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Controller\Dependencies\ViewRenderer;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\IndexActionHelperInterface;
use Jmf\CrudEngine\Controller\Traits\WithActionHelperTrait;
use Jmf\CrudEngine\Controller\Traits\WithEntityManagerTrait;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineViewRenderingException;
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

    /**
     * @psalm-param IndexActionHelperInterface<E> $defaultActionHelper
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        ActionHelperResolver $actionHelperResolver,
        private ActionConfigurationRepositoryInterface $actionConfigurationRepository,
        private ViewRenderer $viewRenderer,
        private IndexActionHelperInterface $defaultActionHelper,
    ) {
        $this->managerRegistry      = $managerRegistry;
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
            $this->defaultActionHelper,
        );

        $actionHelper->hookBeforeRender($request);

        return $this->viewRenderer->render(
            $actionConfiguration,
            $this->getViewContext(
                $request,
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
     */
    private function getViewContext(
        Request $request,
        IndexActionHelperInterface $actionHelper,
        array $defaults,
    ): array {
        return array_merge(
            $actionHelper->getViewVariables($request),
            $defaults,
        );
    }
}
