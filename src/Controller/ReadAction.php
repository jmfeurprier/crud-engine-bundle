<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\ReadActionHelperInterface;
use Jmf\CrudEngine\Controller\Traits\WithActionHelperTrait;
use Jmf\CrudEngine\Controller\Traits\WithEntityManagerTrait;
use Jmf\CrudEngine\Controller\Traits\WithViewTrait;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineViewRenderingException;
use Jmf\TemplateRendering\Exception\TemplateRenderingException;
use Jmf\TemplateRendering\TemplateRendererInterface;
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
    use WithViewTrait;

    /**
     * @param ReadActionHelperInterface<E> $defaultActionHelper
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        TemplateRendererInterface $templateRenderer,
        ReadActionHelperInterface $defaultActionHelper,
        ActionHelperResolver $actionHelperResolver,
        private readonly ActionConfigurationRepositoryInterface $actionConfigurationRepository,
    ) {
        $this->managerRegistry      = $managerRegistry;
        $this->templateRenderer     = $templateRenderer;
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
        string $id,
        string $entityClass,
    ): Response {
        $actionConfiguration = $this->actionConfigurationRepository->get($entityClass, 'read');

        $actionHelper = $this->getActionHelper(
            ReadActionHelperInterface::class,
            $actionConfiguration,
        );

        $entity = $this->getEntity($entityClass, $id);

        return $this->render(
            $actionConfiguration,
            $this->getViewContext(
                $request,
                $actionConfiguration,
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

        if ($entity) {
            return $entity;
        }

        throw new NotFoundHttpException();
    }

    /**
     * @param E                    $entity
     * @param array<string, mixed> $defaults
     *
     * @return array<string, mixed>
     *
     * @throws CrudEngineMissingConfigurationException
     */
    private function getViewContext(
        Request $request,
        ActionConfiguration $actionConfiguration,
        ReadActionHelperInterface $actionHelper,
        object $entity,
        array $defaults,
    ): array {
        return array_merge(
            $actionHelper->getViewVariables($request, $entity),
            $this->mapViewVariables(
                $actionConfiguration,
                $defaults,
            ),
        );
    }
}
