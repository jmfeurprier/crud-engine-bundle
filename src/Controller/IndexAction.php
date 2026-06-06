<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller;

use Jmf\CrudEngine\Configuration\Repository\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\IndexActionHelperInterface;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperNotAnObjectException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperRetrievalException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperTypeMismatchException;
use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineMissingViewException;
use Jmf\CrudEngine\Exception\CrudEngineViewRenderingException;
use Jmf\CrudEngine\Persistence\EntityManagerResolver;
use Jmf\CrudEngine\View\ViewRenderer;
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
     * @psalm-param IndexActionHelperInterface<E> $defaultActionHelper
     */
    public function __construct(
        private ActionConfigurationRepositoryInterface $actionConfigurationRepository,
        private ActionHelperResolver $actionHelperResolver,
        private IndexActionHelperInterface $defaultActionHelper,
        private EntityManagerResolver $objectManagerResolver,
        private ViewRenderer $viewRenderer,
    ) {
    }

    /**
     * @param class-string<E> $entityClass
     *
     * @throws CrudEngineActionHelperNotAnObjectException
     * @throws CrudEngineActionHelperNotFoundException
     * @throws CrudEngineActionHelperRetrievalException
     * @throws CrudEngineActionHelperTypeMismatchException
     * @throws CrudEngineConfigurationException
     * @throws CrudEngineEntityManagerNotFoundException
     * @throws CrudEngineMissingViewException
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
            $actionHelper->getViewVariables($request),
            [
                'entities' => $this->getEntities($request, $entityClass, $actionHelper),
            ],
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
            $this->objectManagerResolver->resolve($entityClass),
        );
    }
}
