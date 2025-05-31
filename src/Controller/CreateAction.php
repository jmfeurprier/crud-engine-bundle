<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\CreateActionHelperInterface;
use Jmf\CrudEngine\Controller\Traits\WithActionHelperTrait;
use Jmf\CrudEngine\Controller\Traits\WithEntityManagerTrait;
use Jmf\CrudEngine\Controller\Traits\WithFormTrait;
use Jmf\CrudEngine\Controller\Traits\WithRedirectionTrait;
use Jmf\CrudEngine\Controller\Traits\WithViewTrait;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineInstantiationFailureException;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\TemplateRendering\Exception\TemplateRenderingException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Override;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @template E of object
 */
#[AsController]
class CreateAction
{
    /**
     * @use WithActionHelperTrait<CreateActionHelperInterface<E>>
     */
    use WithActionHelperTrait;
    use WithEntityManagerTrait;
    use WithFormTrait;
    use WithRedirectionTrait;
    use WithViewTrait;

    private Request $request;

    /**
     * @var CreateActionHelperInterface<E>
     */
    private CreateActionHelperInterface $actionHelper;

    /**
     * @var E
     */
    private object $entity;

    /**
     * @psalm-param CreateActionHelperInterface<E> $defaultActionHelper
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator,
        TemplateRendererInterface $templateRenderer,
        ManagerRegistry $managerRegistry,
        CreateActionHelperInterface $defaultActionHelper,
        ActionHelperResolver $actionHelperResolver,
        private readonly ActionConfigurationRepositoryInterface $actionConfigurationRepository,
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
     * @throws CrudEngineInstantiationFailureException
     * @throws CrudEngineInvalidActionHelperException
     * @throws CrudEngineMissingConfigurationException
     * @throws TemplateRenderingException
     */
    public function __invoke(
        Request $request,
        string $entityClass,
    ): Response {
        $actionConfiguration = $this->actionConfigurationRepository->get($entityClass, 'create');
        $this->request       = $request;
        $this->actionHelper  = $this->getActionHelper(
            CreateActionHelperInterface::class,
            $actionConfiguration,
        );
        $this->entity        = $this->actionHelper->createEntity($request, $entityClass);

        $form = $this->getForm($actionConfiguration, $this->entity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->actionHelper->hookBeforePersist(
                $request,
                $this->entity,
            );

            $this->actionHelper->persist(
                $request,
                $this->entity,
                $this->getEntityManager($entityClass),
            );

            $this->actionHelper->hookAfterPersist(
                $request,
                $this->entity,
            );

            return $this->redirectOnSuccess($actionConfiguration, $this->entity);
        }

        return $this->render(
            $actionConfiguration,
            $this->getViewContext(
                $actionConfiguration,
                [
                    'entity' => $this->entity,
                    'form'   => $form->createView(),
                ],
            ),
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
            $this->actionHelper->getViewVariables($this->request, $this->entity),
            $this->mapViewVariables(
                $actionConfiguration,
                $defaults,
            ),
        );
    }
}
