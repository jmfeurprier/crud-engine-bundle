<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\UpdateActionHelperInterface;
use Jmf\CrudEngine\Controller\Traits\WithActionHelperTrait;
use Jmf\CrudEngine\Controller\Traits\WithEntityManagerTrait;
use Jmf\CrudEngine\Controller\Traits\WithFormTrait;
use Jmf\CrudEngine\Controller\Traits\WithRedirectionTrait;
use Jmf\CrudEngine\Controller\Traits\WithViewTrait;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineRedirectionParameterRenderingException;
use Jmf\CrudEngine\Exception\CrudEngineViewRenderingException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @template E of object
 */
#[AsController]
readonly class UpdateAction
{
    /**
     * @use WithActionHelperTrait<UpdateActionHelperInterface<E>>
     */
    use WithActionHelperTrait;
    use WithEntityManagerTrait;
    use WithFormTrait;
    use WithRedirectionTrait;
    use WithViewTrait;

    /**
     * @psalm-param UpdateActionHelperInterface<E> $defaultActionHelper
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator,
        TemplateRendererInterface $templateRenderer,
        ManagerRegistry $managerRegistry,
        UpdateActionHelperInterface $defaultActionHelper,
        ActionHelperResolver $actionHelperResolver,
        private ActionConfigurationRepositoryInterface $actionConfigurationRepository,
    ) {
        $this->formFactory          = $formFactory;
        $this->urlGenerator         = $urlGenerator;
        $this->templateRenderer     = $templateRenderer;
        $this->managerRegistry      = $managerRegistry;
        $this->defaultActionHelper  = $defaultActionHelper;
        $this->actionHelperResolver = $actionHelperResolver;
    }

    /**
     * @param class-string<E> $entityClass
     *
     * @throws CrudEngineEntityManagerNotFoundException
     * @throws CrudEngineInvalidActionHelperException
     * @throws CrudEngineMissingConfigurationException
     * @throws CrudEngineRedirectionParameterRenderingException
     * @throws CrudEngineViewRenderingException
     */
    public function __invoke(
        Request $request,
        string $entityClass,
        string $id,
    ): Response {
        $actionConfiguration = $this->actionConfigurationRepository->get($entityClass, 'update');
        $actionHelper        = $this->getActionHelper(
            UpdateActionHelperInterface::class,
            $actionConfiguration,
        );

        $entity = $this->getEntity($entityClass, $id);
        $form   = $this->getForm($actionConfiguration, $entity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $actionHelper->hookBeforePersist(
                $request,
                $entity,
                $form,
            );

            $actionHelper->persist(
                $request,
                $entity,
                $form,
                $this->getEntityManager($entityClass),
            );

            $actionHelper->hookAfterPersist(
                $request,
                $entity,
                $form,
            );

            return $this->redirectOnSuccess($actionConfiguration, $entity);
        }

        return $this->render(
            $actionConfiguration,
            $this->getViewContext(
                $actionConfiguration,
                [
                    'entity' => $entity,
                    'form'   => $form->createView(),
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
        return $this->mapViewVariables(
            $actionConfiguration,
            $defaults,
        );
    }
}
