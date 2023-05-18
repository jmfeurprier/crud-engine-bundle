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
use Twig\Environment;

class IndexAction
{
    use WithContainerTrait;
    use WithEntityManagerTrait;
    use WithViewTrait;

    private IndexActionHelperInterface $defaultActionHelper;

    private Request $request;

    private array $actionProperties;

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
     * @throws CrudEngineInvalidActionHelperException
     */
    public function __invoke(
        Request $request,
        string $entityClass,
        array $actionProperties
    ): Response {
        $this->request          = $request;
        $this->actionProperties = $actionProperties;
        $this->actionHelper     = $this->getActionHelper();

        $this->hookBeforeRender($request);

        return $this->render(
            [
                'entities' => $this->getEntities($entityClass),
            ]
        );
    }

    /**
     * @throws CrudEngineInvalidActionHelperException
     */
    private function getActionHelper(): IndexActionHelperInterface
    {
        if (!array_key_exists('helperClass', $this->actionProperties)) {
            return $this->defaultActionHelper;
        }

        $actionHelper = $this->getService($this->actionProperties['helperClass']);

        if ($actionHelper instanceof IndexActionHelperInterface) {
            return $actionHelper;
        }

        throw new CrudEngineInvalidActionHelperException(); // @todo
    }

    private function hookBeforeRender(Request $request): void
    {
        $this->actionHelper->hookBeforeRender($request);
    }

    private function getEntities(string $entityClass): array
    {
        $entityRepository = $this->getRepository($entityClass);

        return $this->actionHelper->getEntities($entityRepository);
    }

    protected function getViewContext(array $defaults): array
    {
        return array_merge(
            $this->actionHelper->getViewVariables($this->request),
            $this->mapViewVariables(
                $this->actionProperties,
                $defaults
            )
        );
    }
}
