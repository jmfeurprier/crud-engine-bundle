<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Controller\Dependencies\ViewRenderer;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\IndexActionHelperInterface;
use Jmf\CrudEngine\Controller\Traits\WithEntityManagerTrait;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
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
    use WithEntityManagerTrait;

    /**
     * @psalm-param IndexActionHelperInterface<E> $defaultActionHelper
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        private ActionHelperResolver $actionHelperResolver,
        private ActionConfigurationRepositoryInterface $actionConfigurationRepository,
        private ViewRenderer $viewRenderer,
        private IndexActionHelperInterface $defaultActionHelper,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param class-string<E> $entityClass
     *
     * @throws CrudEngineEntityManagerNotFoundException
     * @throws CrudEngineInvalidActionHelperException
     * @throws CrudEngineMissingConfigurationException
     * @throws CrudEngineViewRenderingException
     */
    public function __invoke(
        Request $request,
        string $entityClass,
    ): Response {
        $actionConfiguration = $this->actionConfigurationRepository->get($entityClass, 'index');
        $actionHelper        = $this->actionHelperResolver->resolve(
            IndexActionHelperInterface::class,
            $actionConfiguration,
            $this->defaultActionHelper,
        );

        $actionHelper->hookBeforeRender($request);

        return $this->viewRenderer->render(
            $actionConfiguration,
            array_merge(
                $actionHelper->getViewVariables($request),
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
     *
     * @throws CrudEngineEntityManagerNotFoundException
     */
    private function getEntities(
        Request $request,
        string $entityClass,
        IndexActionHelperInterface $actionHelper,
    ): iterable {
        return $actionHelper->getEntities(
            $request,
            $entityClass,
            $this->getEntityManager($entityClass),
        );
    }
}
