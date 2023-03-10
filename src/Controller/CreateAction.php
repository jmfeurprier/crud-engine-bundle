<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Controller\Traits\WithFormTrait;
use Jmf\CrudEngine\Controller\Traits\WithRedirectionTrait;
use Jmf\CrudEngine\Controller\Traits\WithViewTrait;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class CreateAction
{
    use WithFormTrait;
    use WithRedirectionTrait;
    use WithViewTrait;

    private ManagerRegistry $managerRegistry;

    private CreateActionHelperInterface $defaultActionHelper;

    private EntityManagerInterface $entityManager;

    private array $actionProperties;

    private string $entityClass;

    private CreateActionHelperInterface $actionHelper;

    private Request $request;

    public function __construct(
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator,
        Environment $twigEnvironment,
        ManagerRegistry $managerRegistry,
        CreateActionHelperInterface $defaultActionHelper
    ) {
        $this->formFactory         = $formFactory;
        $this->urlGenerator        = $urlGenerator;
        $this->twigEnvironment     = $twigEnvironment;
        $this->managerRegistry     = $managerRegistry;
        $this->defaultActionHelper = $defaultActionHelper;
    }

    /**
     * @throws CrudEngineInvalidActionHelperException
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    public function __invoke(
        Request $request,
        array $actionProperties,
        ContainerInterface $container,
        string $entityClass
    ): Response {
        $this->init($request, $actionProperties, $container, $entityClass);

        $entity = $this->createNewEntity();
        $form   = $this->getForm($this->actionProperties, $entity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            $this->hookAfterPersist($entity);

            return $this->redirectOnSuccess($this->actionProperties, $entity);
        }

        return $this->render(
            [
                'entity' => $entity,
                'form'   => $form->createView(),
            ]
        );
    }

    /**
     * @throws CrudEngineInvalidActionHelperException
     */
    private function init(
        Request $request,
        array $actionProperties,
        ContainerInterface $container,
        string $entityClass
    ): void {
        $this->request          = $request;
        $this->actionProperties = $actionProperties;
        $this->actionHelper     = $this->getActionHelper($container);
        $this->entityManager    = $this->managerRegistry->getManagerForClass($entityClass);
        $this->entityClass      = $entityClass;
    }

    /**
     * @throws CrudEngineInvalidActionHelperException
     */
    private function getActionHelper(ContainerInterface $container): CreateActionHelperInterface
    {
        if (!array_key_exists('helperClass', $this->actionProperties)) {
            return $this->defaultActionHelper;
        }

        $actionHelper = $container->get($this->actionProperties['helperClass']);

        if ($actionHelper instanceof CreateActionHelperInterface) {
            return $actionHelper;
        }

        throw new CrudEngineInvalidActionHelperException();
    }

    private function createNewEntity(): object
    {
        return $this->actionHelper->createEntity($this->request, $this->entityClass);
    }

    private function hookAfterPersist(object $entity): void
    {
        $this->actionHelper->hookAfterPersist($this->request, $entity);
    }

    protected function getViewContext(array $defaults): array
    {
        return $this->mapViewVariables(
            $this->actionProperties,
            $defaults
        );
    }
}
