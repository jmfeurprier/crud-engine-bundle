<?php

namespace Jmf\CrudEngine\Configuration;

use Override;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

readonly class CacheableActionConfigurationRepositoryFactory implements ActionConfigurationRepositoryFactoryInterface
{
    public function __construct(
        private CacheInterface $cache,
        private ActionConfigurationRepositoryFactoryInterface $actionConfigurationRepositoryFactory,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Override]
    public function make(): ActionConfigurationRepositoryInterface
    {
        return $this->cache->get(
            $this->getCacheKey(),
            $this->getCallback(),
        );
    }

    private function getCacheKey(): string
    {
        return md5(
            serialize(
                [
                    self::class,
                ]
            )
        );
    }

    private function getCallback(): callable
    {
        return fn(
            ItemInterface $item,
        ) => $this->actionConfigurationRepositoryFactory->make();
    }
}
