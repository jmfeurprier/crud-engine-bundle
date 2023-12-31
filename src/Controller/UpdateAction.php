<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepository;
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
use Override;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * @template E of object
 */
#[AsController]
class UpdateAction
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
        Environment $twigEnvironment,
        ManagerRegistry $managerRegistry,
        UpdateActionHelperInterface $defaultActionHelper,
        ActionHelperResolver $actionHelperResolver,
        private readonly ActionConfigurationRepository $actionConfigurationRepository,
    ) {
        $this->formFactory          = $formFactory;
        $this->urlGenerator         = $urlGenerator;
        $this->twigEnvironment      = $twigEnvironment;
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
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
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
            $this->getEntityManager($entityClass)->flush();

            $actionHelper->hookAfterPersist($request, $entity);

            return $this->redirectOnSuccess($actionConfiguration, $entity);
        }

        return $this->render(
            $actionConfiguration,
            [
                'entity' => $entity,
                'form'   => $form->createView(),
            ],
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
    #[Override]
    protected function getViewContext(
        ActionConfiguration $actionConfiguration,
        array $defaults,
    ): array {
        return $this->mapViewVariables(
            $actionConfiguration,
            $defaults,
        );
    }
}
