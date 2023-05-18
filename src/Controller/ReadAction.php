<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Controller\Traits\WithContainerTrait;
use Jmf\CrudEngine\Controller\Traits\WithEntityManagerTrait;
use Jmf\CrudEngine\Controller\Traits\WithViewTrait;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

class ReadAction
{
    use WithContainerTrait;
    use WithEntityManagerTrait;
    use WithViewTrait;

    private ReadActionHelperInterface $defaultActionHelper;

    private Request $request;

    private array $actionProperties;

    private ReadActionHelperInterface $actionHelper;

    private object $entity;

    public function __construct(
        ManagerRegistry $managerRegistry,
        Environment $twigEnvironment,
        ReadActionHelperInterface $defaultActionHelper,
        ContainerInterface $container
    ) {
        $this->managerRegistry     = $managerRegistry;
        $this->twigEnvironment     = $twigEnvironment;
        $this->defaultActionHelper = $defaultActionHelper;
        $this->container           = $container;
    }

    public function __invoke(
        Request $request,
        string $id,
        string $entityClass,
        array $actionProperties
    ): Response {
        $this->request          = $request;
        $this->actionProperties = $actionProperties;
        $this->actionHelper     = $this->getActionHelper();
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
        $entity = $this->getRepository($entityClass)->find($id);

        if ($entity) {
            return $entity;
        }

        throw new NotFoundHttpException();
    }

    /**
     * @throws CrudEngineInvalidActionHelperException
     */
    private function getActionHelper(): ReadActionHelperInterface
    {
        if (!array_key_exists('helperClass', $this->actionProperties)) {
            return $this->defaultActionHelper;
        }

        $actionHelper = $this->getService($this->actionProperties['helperClass']);

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
