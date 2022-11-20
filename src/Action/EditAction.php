<?php

namespace Jmf\CrudEngine\Action;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

class EditAction extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private Environment $twigEnvironment;

    private array $actionProperties;

    public function __construct(
        EntityManagerInterface $entityManager,
        Environment $twigEnvironment
    ) {
        $this->entityManager = $entityManager;
        $this->twigEnvironment = $twigEnvironment;
    }

    public function __invoke(
        Request $request,
        array $actionProperties,
        string $entityClass,
        string $id
    ): Response {
        $entity = $this->getEntity($entityClass, $id);

        if (!$entity) {
            throw new NotFoundHttpException(); // @todo
        }

        $this->actionProperties = $actionProperties;

        $form = $this->createForm($this->getFormTypeClass(), $entity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute(
                $this->getRedirectRoute($entity),
                $this->getRedirectRouteParameters($entity)
            );
        }

        return $this->render(
            $this->getViewPath(),
            [
                'entity' => $entity,
                'form'   => $form->createView(),
            ]
        );
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getEntity(
        string $entityClass,
        string $id
    ): object {
        $entity = $this->entityManager->find($entityClass, $id);

        if ($entity) {
            return $entity;
        }

        throw new NotFoundHttpException();
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
        $definitions = $this->actionProperties['redirection']['parameters']
            ?? [];
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
