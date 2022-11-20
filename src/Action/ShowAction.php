<?php

namespace Jmf\CrudEngine\Action;

use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShowAction extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private ShowActionHelperInterface $defaultActionHelper;

    private array $actionProperties;

    private ShowActionHelperInterface $actionHelper;

    public function __construct(
        EntityManagerInterface $entityManager,
        ShowActionHelperDefault $defaultActionHelper
    ) {
        $this->entityManager = $entityManager;
        $this->defaultActionHelper = $defaultActionHelper;
    }

    public function __invoke(
        Request $request,
        string $id,
        string $entityClass,
        array $actionProperties,
        ContainerInterface $container // @xxx
    ): Response {
        $entity = $this->getEntity($entityClass, $id);

        $this->actionProperties = $actionProperties;
        $this->actionHelper     = $this->getActionHelper($container);

        $viewParameters = array_merge(
            $this->getViewParameters($request, $entity),
            [
                'entity' => $entity,
            ]
        );

        return $this->render(
            $this->getViewPath(),
            $viewParameters
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

    private function getActionHelper(ContainerInterface $container): ShowActionHelperInterface
    {
        if (!array_key_exists('helperClass', $this->actionProperties)) {
            return $this->defaultActionHelper;
        }

        $actionHelper = $container->get($this->actionProperties['helperClass']);

        if ($actionHelper instanceof ShowActionHelperInterface) {
            return $actionHelper;
        }

        throw new CrudEngineInvalidActionHelperException(); // @todo
    }

    private function getViewPath(): string
    {
        return $this->actionProperties['viewPath'];
    }

    private function getViewParameters(
        Request $request,
        object $entity
    ): array {
        return $this->actionHelper->getViewParameters($request, $entity);
    }
}
