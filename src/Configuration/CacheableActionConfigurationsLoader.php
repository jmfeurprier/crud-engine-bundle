<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration;

use Override;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Webmozart\Assert\Assert;

readonly class CacheableActionConfigurationsLoader implements ActionConfigurationsLoaderInterface
{
    public function __construct(
        private CacheInterface $cache,
        private ActionConfigurationsLoaderInterface $actionConfigurationsLoader,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Override]
    public function load(array $config): ActionConfigurationsCollection
    {
        $actionConfigurations = $this->cache->get(
            $this->getCacheKey(),
            $this->getCallback($config),
        );

        Assert::isInstanceOf($actionConfigurations, ActionConfigurationsCollection::class);

        return $actionConfigurations;
    }

    private function getCacheKey(): string
    {
        return md5(
            serialize(
                [
                    self::class,
                ],
            ),
        );
    }

    /**
     * @param array<string, mixed> $config
     */
    private function getCallback(array $config): callable
    {
        return fn(
            ItemInterface $item,
        ): ActionConfigurationsCollection => $this->actionConfigurationsLoader->load($config);
    }
}
