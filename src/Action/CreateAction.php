<?php

namespace Jmf\CrudEngine\Action;

use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class CreateAction extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private Environment $twigEnvironment;

    private CreateActionHelperInterface $defaultActionHelper;

    private Request $request;

    private string $entityClass;

    private array $actionProperties;

    private CreateActionHelperInterface $actionHelper;

    public function __construct(
        EntityManagerInterface $entityManager,
        Environment $twigEnvironment
    ) {
        $this->entityManager       = $entityManager;
        $this->twigEnvironment     = $twigEnvironment;
        $this->defaultActionHelper = new CreateActionHelperDefault();
    }

    public function __invoke(
        Request $request,
        string $entityClass,
        array $actionProperties,
        ContainerInterface $container
    ): Response {
        $this->init($request, $entityClass, $actionProperties, $container);

        $entity = $this->createNewEntity();
        $form   = $this->getForm($entity);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            return $this->redirectToRoute(
                $this->getRedirectRoute(),
                $this->getRedirectRouteParameters($entity)
            );
        }

        return $this->render(
            $this->getViewPath(),
            [
                'form' => $form->createView(),
            ]
        );
    }

    private function init(
        Request $request,
        string $entityClass,
        array $actionProperties,
        ContainerInterface $container
    ): void {
        $this->request          = $request;
        $this->entityClass      = $entityClass;
        $this->actionProperties = $actionProperties;
        $this->actionHelper     = $this->getActionHelper($container);
    }

    /**
     * @throws CrudEngineInvalidActionHelperException
     */
    private function getActionHelper(
        ContainerInterface $container
    ): CreateActionHelperInterface {
        if (!array_key_exists('helperClass', $this->actionProperties)) {
            return $this->defaultActionHelper;
        }

        $actionHelper = $container->get($this->actionProperties['helperClass']);

        if ($actionHelper instanceof CreateActionHelperInterface) {
            return $actionHelper;
        }

        throw new CrudEngineInvalidActionHelperException(); // @todo
    }

    private function createNewEntity(): object
    {
        return $this->actionHelper->createEntity($this->request, $this->entityClass);
    }

    private function getForm(object $entity): FormInterface
    {
        return $this->createForm($this->getFormTypeClass(), $entity);
    }

    private function getFormTypeClass(): string
    {
        return $this->actionProperties['formTypeClass'];
    }

    private function getRedirectRoute(): string
    {
        return $this->actionProperties['redirection']['route'];
    }

    private function getRedirectRouteParameters(object $entity): array
    {
        $definitions = $this->actionProperties['redirection']['parameters'] ?? [];
        $parameters  = [];

        foreach ($definitions as $key => $definition) {
            $template = $this->twigEnvironment->createTemplate($definition);

            $parameters[$key] = $template->render(
                [
                    '_entity' => $entity,
                ]
            );
        }

        return $parameters;
    }

    private function getViewPath(): string
    {
        return $this->actionProperties['viewPath'];
    }
}
