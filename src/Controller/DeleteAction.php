<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Controller;

use Jmf\CrudEngine\Configuration\Repository\ActionConfigurationRepositoryInterface;
use Jmf\CrudEngine\Controller\Helpers\ActionHelperResolver;
use Jmf\CrudEngine\Controller\Helpers\DeleteActionHelperInterface;
use Jmf\CrudEngine\Exception\CrudEngineConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineEntityManagerNotFoundException;
use Jmf\CrudEngine\Exception\CrudEngineInvalidActionHelperException;
use Jmf\CrudEngine\Exception\CrudEngineRedirectionParameterRenderingException;
use Jmf\CrudEngine\Exception\CrudEngineViewRenderingException;
use Jmf\CrudEngine\Persistence\EntityFinder;
use Jmf\CrudEngine\Persistence\EntityManagerResolver;
use Jmf\CrudEngine\Redirection\RedirectionGenerator;
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
        private ActionConfigurationRepositoryInterface $actionConfigurationRepository,
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
     * @throws CrudEngineConfigurationException
     * @throws CrudEngineEntityManagerNotFoundException
     * @throws CrudEngineInvalidActionHelperException
     * @throws CrudEngineRedirectionParameterRenderingException
     * @throws CrudEngineViewRenderingException
     * @throws Throwable
     */
    public function __invoke(
        Request $request,
        string $entityClass,
        string $id,
    ): Response {
        $actionConfiguration = $this->actionConfigurationRepository->get($entityClass, 'delete');
        $actionHelper        = $this->actionHelperResolver->resolve(
            DeleteActionHelperInterface::class,
            $actionConfiguration,
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

            return $this->redirectionGenerator->generate($actionConfiguration, $entity);
        }

        return $this->viewRenderer->render(
            $actionConfiguration,
            $actionHelper->getViewVariables($request, $entity),
            [
                'entity' => $entity,
            ],
        );
    }
}
