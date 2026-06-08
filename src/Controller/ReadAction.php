<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller;

use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\ReadActionHelperInterface;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperNotAnObjectException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperRetrievalException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperTypeMismatchException;
use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineMissingViewException;
use Jmf\CrudEngine\Exception\CrudEnginePersistenceException;
use Jmf\CrudEngine\Exception\CrudEngineViewRenderingException;
use Jmf\CrudEngine\Model\CrudAction;
use Jmf\CrudEngine\Persistence\EntityFinder;
use Jmf\CrudEngine\Registry\ActionDefinitionRegistryInterface;
use Jmf\CrudEngine\View\ViewRenderer;
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
        private ActionDefinitionRegistryInterface $actionDefinitionRegistry,
        private ActionHelperResolver $actionHelperResolver,
        private ReadActionHelperInterface $defaultActionHelper,
        private EntityFinder $entityFinder,
        private ViewRenderer $viewRenderer,
    ) {
    }

    /**
     * @param class-string<E> $entityClass
     *
     * @throws CrudEngineActionHelperNotAnObjectException
     * @throws CrudEngineActionHelperNotFoundException
     * @throws CrudEngineActionHelperRetrievalException
     * @throws CrudEngineActionHelperTypeMismatchException
     * @throws CrudEngineConfigurationException
     * @throws CrudEngineEntityManagerNotFoundException
     * @throws CrudEngineMissingViewException
     * @throws CrudEnginePersistenceException
     * @throws CrudEngineViewRenderingException
     */
    public function __invoke(
        Request $request,
        string $id,
        string $entityClass,
    ): Response {
        $actionDefinition = $this->actionDefinitionRegistry->get(
            $entityClass,
            CrudAction::Read,
        );

        $actionHelper = $this->actionHelperResolver->resolve(
            ReadActionHelperInterface::class,
            $actionDefinition,
            $this->defaultActionHelper,
        );

        $entity = $this->entityFinder->find($entityClass, $id);

        return $this->viewRenderer->render(
            $actionDefinition,
            $actionHelper->getViewVariables($request, $entity),
            [
                'entity' => $entity,
            ],
        );
    }
}
