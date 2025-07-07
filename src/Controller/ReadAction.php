<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Controller\Dependencies\ViewRenderer;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\ReadActionHelperInterface;
use Jmf\CrudEngine\Controller\Traits\WithEntityManagerTrait;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineViewRenderingException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @template E of object
 */
#[AsController]
readonly class ReadAction
{
    use WithEntityManagerTrait;

    /**
     * @psalm-param ReadActionHelperInterface<E> $defaultActionHelper
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        private ActionHelperResolver $actionHelperResolver,
        private ActionConfigurationRepositoryInterface $actionConfigurationRepository,
        private ViewRenderer $viewRenderer,
        private ReadActionHelperInterface $defaultActionHelper,
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
        string $id,
        string $entityClass,
    ): Response {
        $actionConfiguration = $this->actionConfigurationRepository->get($entityClass, 'read');
        $actionHelper        = $this->actionHelperResolver->resolve(
            ReadActionHelperInterface::class,
            $actionConfiguration,
            $this->defaultActionHelper,
        );

        $entity = $this->getEntity($entityClass, $id);

        return $this->viewRenderer->render(
            $actionConfiguration,
            array_merge(
                $actionHelper->getViewVariables($request, $entity),
                [
                    'entity' => $entity,
                ],
            ),
        );
    }

    /**
     * @param class-string<E> $entityClass
     *
     * @psalm-return E
     *
     * @throws CrudEngineEntityManagerNotFoundException
     * @throws NotFoundHttpException
     */
    private function getEntity(
        string $entityClass,
        string $id,
    ): object {
        $entity = $this->getEntityManager($entityClass)->find($entityClass, $id);

        return $entity ?? throw new NotFoundHttpException();
    }
}
