<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Controller\Traits\WithActionHelperTrait;
use Jmf\CrudEngine\Controller\Traits\WithEntityManagerTrait;
use Jmf\CrudEngine\Controller\Traits\WithViewTrait;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ReadAction
{
    /**
     * @use WithActionHelperTrait<ReadActionHelperInterface>
     */
    use WithActionHelperTrait;
    use WithEntityManagerTrait;
    use WithViewTrait;

    private Request $request;

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

    /**
     * @param class-string         $entityClass
     * @param array<string, mixed> $actionProperties
     *
     * @throws CrudEngineInvalidActionHelperException
     * @throws CrudEngineInvalidConfigurationException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(
        Request $request,
        string $id,
        string $entityClass,
        array $actionProperties
    ): Response {
        $this->request      = $request;
        $this->actionHelper = $this->getActionHelper(ReadActionHelperInterface::class, $actionProperties);
        $this->entity       = $this->getEntity($entityClass, $id);

        return $this->render(
            $actionProperties,
            [
                'entity' => $this->entity,
            ]
        );
    }

    /**
     * @param class-string $entityClass
     *
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
     * @param array<string, mixed> $actionProperties
     * @param array<string, mixed> $defaults
     *
     * @return array<string, mixed>
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    protected function getViewContext(
        array $actionProperties,
        array $defaults
    ): array {
        return array_merge(
            $this->actionHelper->getViewVariables($this->request, $this->entity),
            $this->mapViewVariables(
                $actionProperties,
                $defaults
            )
        );
    }
}
