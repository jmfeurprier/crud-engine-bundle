<?php

namespace Jmf\CrudEngine\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Jmf\CrudEngine\Configuration\ActionConfiguration;
use Jmf\CrudEngine\Configuration\ActionConfigurationRepository;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\ReadActionHelperInterface;
use Jmf\CrudEngine\Controller\Traits\WithActionHelperTrait;
use Jmf\CrudEngine\Controller\Traits\WithEntityManagerTrait;
use Jmf\CrudEngine\Controller\Traits\WithViewTrait;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * @template E of object
 */
#[AsController]
class ReadAction
{
    /**
     * @use WithActionHelperTrait<ReadActionHelperInterface<E>>
     */
    use WithActionHelperTrait;
    use WithEntityManagerTrait;
    use WithViewTrait;

    private Request $request;

    /**
     * @var ReadActionHelperInterface<E>
     */
    private ReadActionHelperInterface $actionHelper;

    /**
     * @var E
     */
    private object $entity;

    /**
     * @param ReadActionHelperInterface<E> $defaultActionHelper
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        Environment $twigEnvironment,
        ReadActionHelperInterface $defaultActionHelper,
        ActionHelperResolver $actionHelperResolver,
        private readonly ActionConfigurationRepository $actionConfigurationRepository,
    ) {
        $this->managerRegistry      = $managerRegistry;
        $this->twigEnvironment      = $twigEnvironment;
        $this->defaultActionHelper  = $defaultActionHelper;
        $this->actionHelperResolver = $actionHelperResolver;
    }

    /**
     * @param class-string<E> $entityClass
     *
     * @throws CrudEngineInvalidActionHelperException
     * @throws CrudEngineMissingConfigurationException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(
        Request $request,
        string $id,
        string $entityClass,
    ): Response {
        $actionConfiguration = $this->actionConfigurationRepository->get($entityClass, 'read');
        $this->request       = $request;
        $this->actionHelper  = $this->getActionHelper(
            ReadActionHelperInterface::class,
            $actionConfiguration,
        );
        $this->entity        = $this->getEntity($entityClass, $id);

        return $this->render(
            $actionConfiguration,
            [
                'entity' => $this->entity,
            ]
        );
    }

    /**
     * @param class-string<E> $entityClass
     *
     * @psalm-return E
     *
     * @throws NotFoundHttpException
     */
    private function getEntity(
        string $entityClass,
        string $id,
    ): object {
        $entity = $this->getRepository($entityClass)->find($id);

        if ($entity) {
            return $entity;
        }

        throw new NotFoundHttpException();
    }

    /**
     * @param array<string, mixed> $defaults
     *
     * @return array<string, mixed>
     *
     * @throws CrudEngineMissingConfigurationException
     */
    #[Override]
    protected function getViewContext(
        ActionConfiguration $actionConfiguration,
        array $defaults,
    ): array {
        return array_merge(
            $this->actionHelper->getViewVariables($this->request, $this->entity),
            $this->mapViewVariables(
                $actionConfiguration,
                $defaults,
            )
        );
    }
}
