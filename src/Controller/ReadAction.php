<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Controller\Traits\WithViewTrait;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

class ReadAction
{
    use WithViewTrait;

    private ManagerRegistry $managerRegistry;

    private ReadActionHelperInterface $defaultActionHelper;

    private Request $request;

    private array $actionProperties;

    private EntityManagerInterface $entityManager;

    private ReadActionHelperInterface $actionHelper;

    private object $entity;

    public function __construct(
        ManagerRegistry $managerRegistry,
        Environment $twigEnvironment,
        ReadActionHelperDefault $defaultActionHelper
    ) {
        $this->managerRegistry     = $managerRegistry;
        $this->twigEnvironment     = $twigEnvironment;
        $this->defaultActionHelper = $defaultActionHelper;
    }

    public function __invoke(
        Request $request,
        string $id,
        string $entityClass,
        array $actionProperties,
        ContainerInterface $container // @xxx
    ): Response
    {
        $this->request          = $request;
        $this->actionProperties = $actionProperties;
        $this->entityManager    = $this->managerRegistry->getManagerForClass($entityClass);
        $this->actionHelper     = $this->getActionHelper($container);
        $this->entity           = $this->getEntity($entityClass, $id);

        return $this->render(
            [
                'entity' => $this->entity,
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

    /**
     * @throws CrudEngineInvalidActionHelperException
     */
    private function getActionHelper(ContainerInterface $container): ReadActionHelperInterface
    {
        if (!array_key_exists('helperClass', $this->actionProperties)) {
            return $this->defaultActionHelper;
        }

        $actionHelper = $container->get($this->actionProperties['helperClass']);

        if ($actionHelper instanceof ReadActionHelperInterface) {
            return $actionHelper;
        }

        throw new CrudEngineInvalidActionHelperException(); // @todo
    }

    protected function getViewContext(array $defaults): array
    {
        return array_merge(
            $this->actionHelper->getViewVariables($this->request, $this->entity),
            $this->mapViewVariables(
                $this->actionProperties,
                $defaults
            )
        );
    }
}
