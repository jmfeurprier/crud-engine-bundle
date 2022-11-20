<?php

namespace Jmf\CrudEngine\Action;

use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexAction extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private IndexActionHelperInterface $defaultActionHelper;

    private ObjectRepository $entityRepository;

    private array $actionProperties;

    private IndexActionHelperInterface $actionHelper;

    public function __construct(
        EntityManagerInterface $entityManager,
        IndexActionHelperDefault $defaultActionHelper
    ) {
        $this->entityManager = $entityManager;
        $this->defaultActionHelper = $defaultActionHelper;
    }

    public function __invoke(
        Request $request,
        string $entityClass,
        array $actionProperties,
        ContainerInterface $container // @xxx
    ): Response {
        $this->actionProperties = $actionProperties;
        $this->actionHelper     = $this->getActionHelper($container);
        $this->entityRepository = $this->entityManager->getRepository($entityClass);

        $this->hookPre($request);

        $viewParameters = array_merge(
            $this->getViewParameters($request),
            [
                'entities' => $this->getEntities($this->entityRepository),
            ]
        );

        return $this->render(
            $this->getViewPath(),
            $viewParameters
        );
    }

    private function getActionHelper(ContainerInterface $container): IndexActionHelperInterface
    {
        if (!array_key_exists('helperClass', $this->actionProperties)) {
            return $this->defaultActionHelper;
        }

        $actionHelper = $container->get($this->actionProperties['helperClass']);

        if ($actionHelper instanceof IndexActionHelperInterface) {
            return $actionHelper;
        }

        throw new CrudEngineInvalidActionHelperException(); // @todo
    }

    private function hookPre(Request $request): void
    {
        $this->actionHelper->hookPre($request);
    }

    private function getEntities(ObjectRepository $entityRepository): array
    {
        return $this->actionHelper->getEntities($entityRepository);
    }

    private function getViewPath(): string
    {
        return $this->actionProperties['viewPath'];
    }

    private function getViewParameters(Request $request): array
    {
        return $this->actionHelper->getViewParameters($request);
    }
}
