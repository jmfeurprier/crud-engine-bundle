<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Controller\Traits\WithEntityManagerTrait;
use Jmf\CrudEngine\Controller\Traits\WithFormTrait;
use Jmf\CrudEngine\Controller\Traits\WithRedirectionTrait;
use Jmf\CrudEngine\Controller\Traits\WithViewTrait;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class UpdateAction
{
    use WithEntityManagerTrait;
    use WithFormTrait;
    use WithRedirectionTrait;
    use WithViewTrait;

    private array $actionProperties;

    public function __construct(
        ManagerRegistry $managerRegistry,
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator,
        Environment $twigEnvironment
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->formFactory     = $formFactory;
        $this->urlGenerator    = $urlGenerator;
        $this->twigEnvironment = $twigEnvironment;
    }

    public function __invoke(
        Request $request,
        array $actionProperties,
        string $entityClass,
        string $id
    ): Response {
        $this->actionProperties = $actionProperties;

        $entity = $this->getEntity($entityClass, $id);
        $form   = $this->getForm($actionProperties, $entity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getEntityManager($entityClass)->flush();

            return $this->redirectOnSuccess($actionProperties, $entity);
        }

        return $this->render(
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
        $entity = $this->getRepository($entityClass)->find($id);

        if ($entity) {
            return $entity;
        }

        throw new NotFoundHttpException();
    }

    protected function getViewContext(array $defaults): array
    {
        return $this->mapViewVariables(
            $this->actionProperties,
            $defaults
        );
    }
}
