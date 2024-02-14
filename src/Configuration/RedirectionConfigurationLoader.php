<?php

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Webmozart\Assert\Assert;

class RedirectionConfigurationLoader
{
    /**
     * @var array<string, mixed>
     */
    private array $redirectionConfig;

    /**
     * @param array<string, mixed> $actionConfig
     *
     * @throws CrudEngineMissingConfigurationException
     */
    public function load(array $actionConfig): ?RedirectionConfiguration
    {
        if (!array_key_exists('redirection', $actionConfig)) {
            return null;
        }

        Assert::isMap($actionConfig['redirection']);

        $this->redirectionConfig = $actionConfig['redirection'];

        return new RedirectionConfiguration(
            $this->getRoute(),
            $this->getParameters(),
            $this->getFragment(),
        );
    }

    /**
     * @throws CrudEngineMissingConfigurationException
     */
    private function getRoute(): string
    {
        if (!array_key_exists('route', $this->redirectionConfig)) {
            throw new CrudEngineMissingConfigurationException("Missing required 'route' configuration.");
        }

        Assert::string($this->redirectionConfig['route']);

        return $this->redirectionConfig['route'];
    }

    private function getParameters(): KeyStringCollection
    {
        if (!array_key_exists('parameters', $this->redirectionConfig)) {
            return KeyStringCollection::createEmpty();
        }

        Assert::isArray($this->redirectionConfig['parameters']);

        return new KeyStringCollection(
            $this->redirectionConfig['parameters'],
        );
    }

    private function getFragment(): ?string
    {
        if (!array_key_exists('fragment', $this->redirectionConfig)) {
            return null;
        }

        Assert::string($this->redirectionConfig['fragment']);

        return $this->redirectionConfig['fragment'];
    }
}
