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
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class IndexAction
{
    /**
     * @use WithActionHelperTrait<IndexActionHelperInterface>
     */
    use WithActionHelperTrait;
    use WithEntityManagerTrait;
    use WithViewTrait;

    private Request $request;

    private IndexActionHelperInterface $actionHelper;

    public function __construct(
        Environment $twigEnvironment,
        ManagerRegistry $managerRegistry,
        IndexActionHelperInterface $defaultActionHelper,
        ContainerInterface $container
    ) {
        $this->twigEnvironment     = $twigEnvironment;
        $this->managerRegistry     = $managerRegistry;
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
        string $entityClass,
        array $actionProperties
    ): Response {
        $this->request      = $request;
        $this->actionHelper = $this->getActionHelper(IndexActionHelperInterface::class, $actionProperties);

        $this->hookBeforeRender($this->actionHelper, $request);

        return $this->render(
            $actionProperties,
            [
                'entities' => $this->getEntities($this->actionHelper, $entityClass),
            ]
        );
    }

    private function hookBeforeRender(
        IndexActionHelperInterface $actionHelper,
        Request $request
    ): void {
        $actionHelper->hookBeforeRender($request);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $entityClass
     *
     * @return T[]
     */
    private function getEntities(
        IndexActionHelperInterface $actionHelper,
        string $entityClass
    ): array {
        return $actionHelper->getEntities(
            $this->getRepository($entityClass)
        );
    }

    /**
     * @param array<string, mixed> $actionProperties
     * @param array<string, mixed> $defaults
     *
     * @return array<string, mixed>
     */
    protected function getViewContext(
        array $actionProperties,
        array $defaults
    ): array {
        return array_merge(
            $this->actionHelper->getViewVariables($this->request),
            $this->mapViewVariables(
                $actionProperties,
                $defaults
            )
        );
    }
}
