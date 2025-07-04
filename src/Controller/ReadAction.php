<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Controller\Dependencies\ViewRenderer;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\ReadActionHelperInterface;
use Jmf\CrudEngine\Controller\Traits\WithActionHelperTrait;
use Jmf\CrudEngine\Controller\Traits\WithEntityManagerTrait;
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
    /**
     * @use WithActionHelperTrait<ReadActionHelperInterface<E>>
     */
    use WithActionHelperTrait;
    use WithEntityManagerTrait;

    /**
     * @psalm-param ReadActionHelperInterface<E> $defaultActionHelper
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        ActionHelperResolver $actionHelperResolver,
        private ActionConfigurationRepositoryInterface $actionConfigurationRepository,
        private ViewRenderer $viewRenderer,
        private ReadActionHelperInterface $defaultActionHelper,
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
        string $id,
        string $entityClass,
    ): Response {
        $actionConfiguration = $this->actionConfigurationRepository->get($entityClass, 'read');

        $actionHelper = $this->getActionHelper(
            ReadActionHelperInterface::class,
            $actionConfiguration,
            $this->defaultActionHelper,
        );

        $entity = $this->getEntity($entityClass, $id);

        return $this->viewRenderer->render(
            $actionConfiguration,
            $this->getViewContext(
                $request,
                $actionHelper,
                $entity,
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
     * @throws NotFoundHttpException
     */
    private function getEntity(
        string $entityClass,
        string $id,
    ): object {
        $entity = $this->getRepository($entityClass)->find($id);

        return $entity ?? throw new NotFoundHttpException();
    }

    /**
     * @psalm-param ReadActionHelperInterface<E> $actionHelper
     * @psalm-param E                            $entity
     * @psalm-param array<string, mixed>         $defaults
     *
     * @return array<string, mixed>
     */
    private function getViewContext(
        Request $request,
        ReadActionHelperInterface $actionHelper,
        object $entity,
        array $defaults,
    ): array {
        return array_merge(
            $actionHelper->getViewVariables($request, $entity),
            $defaults,
        );
    }
}
