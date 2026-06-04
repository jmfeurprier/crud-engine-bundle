<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller;

use Jmf\CrudEngine\Configuration\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Controller\Dependencies\EntityFinder;
use Jmf\CrudEngine\Controller\Dependencies\ViewRenderer;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\ReadActionHelperInterface;
use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Jmf\CrudEngine\Exception\CrudEngineMissingViewException;
use Jmf\CrudEngine\Exception\CrudEnginePersistenceException;
use Jmf\CrudEngine\Exception\CrudEngineViewRenderingException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * @template E of object
 */
#[AsController]
readonly class ReadAction
{
    /**
     * @psalm-param ReadActionHelperInterface<E> $defaultActionHelper
     */
    public function __construct(
        private ActionConfigurationRepositoryInterface $actionConfigurationRepository,
        private ActionHelperResolver $actionHelperResolver,
        private ReadActionHelperInterface $defaultActionHelper,
        private EntityFinder $entityFinder,
        private ViewRenderer $viewRenderer,
    ) {
    }

    /**
     * @param class-string<E> $entityClass
     *
     * @throws CrudEngineConfigurationException
     * @throws CrudEngineEntityManagerNotFoundException
     * @throws CrudEngineInvalidActionHelperException
     * @throws CrudEngineMissingViewException
     * @throws CrudEnginePersistenceException
     * @throws CrudEngineViewRenderingException
     */
    public function __invoke(
        Request $request,
        string $id,
        string $entityClass,
    ): Response {
        $actionConfiguration = $this->actionConfigurationRepository->get($entityClass, 'read');
        $actionHelper        = $this->actionHelperResolver->resolve(
            ReadActionHelperInterface::class,
            $actionConfiguration,
            $this->defaultActionHelper,
        );

        $entity = $this->entityFinder->find($entityClass, $id);

        return $this->viewRenderer->render(
            $actionConfiguration,
            $actionHelper->getViewVariables($request, $entity),
            [
                'entity' => $entity,
            ],
        );
    }
}
