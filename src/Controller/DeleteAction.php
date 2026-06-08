<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller;

use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\DeleteActionHelperInterface;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperNotAnObjectException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperRetrievalException;
use Jmf\CrudEngine\Exception\CrudEngineActionHelperTypeMismatchException;
use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerTypeMismatchException;
use Jmf\CrudEngine\Exception\CrudEngineRedirectionParameterRenderingException;
use Jmf\CrudEngine\Exception\CrudEngineViewRenderingException;
use Jmf\CrudEngine\Model\CrudAction;
use Jmf\CrudEngine\Persistence\EntityFinder;
use Jmf\CrudEngine\Persistence\EntityManagerResolver;
use Jmf\CrudEngine\Redirection\RedirectionGenerator;
use Jmf\CrudEngine\Registry\ActionDefinitionRegistryInterface;
use Jmf\CrudEngine\View\ViewRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Throwable;

/**
 * @template E of object
 */
#[AsController]
readonly class DeleteAction
{
    /**
     * @param DeleteActionHelperInterface<E> $defaultActionHelper
     */
    public function __construct(
        private ActionDefinitionRegistryInterface $actionDefinitionRegistry,
        private ActionHelperResolver $actionHelperResolver,
        private DeleteActionHelperInterface $defaultActionHelper,
        private EntityFinder $entityFinder,
        private EntityManagerResolver $objectManagerResolver,
        private RedirectionGenerator $redirectionGenerator,
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
     * @throws CrudEngineEntityManagerTypeMismatchException
     * @throws CrudEngineRedirectionParameterRenderingException
     * @throws CrudEngineViewRenderingException
     * @throws Throwable
     */
    public function __invoke(
        Request $request,
        string $entityClass,
        string $id,
    ): Response {
        $actionDefinition = $this->actionDefinitionRegistry->get(
            $entityClass,
            CrudAction::Delete,
        );

        $actionHelper = $this->actionHelperResolver->resolve(
            DeleteActionHelperInterface::class,
            $actionDefinition,
            $this->defaultActionHelper,
        );

        $entity = $this->entityFinder->find($entityClass, $id);

        if ($request->isMethod('POST')) {
            try {
                $actionHelper->hookBeforeRemove(
                    $entity,
                );

                $actionHelper->remove(
                    $this->objectManagerResolver->resolve($entityClass),
                    $entity,
                );

                $actionHelper->hookAfterRemove(
                    $entity,
                );
            } catch (Throwable $e) {
                return $actionHelper->onFailure($entity, $e);
            }

            return $this->redirectionGenerator->generate($actionDefinition, $entity);
        }

        return $this->viewRenderer->render(
            $actionDefinition,
            $actionHelper->getViewVariables($request, $entity),
            [
                'entity' => $entity,
            ],
        );
    }
}
