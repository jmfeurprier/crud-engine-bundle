<?php

namespace Jmf\CrudEngine\Configuration;

use RuntimeException;
use Webmozart\Assert\Assert;

class ViewConfigurationLoader
{
    /**
     * @var array<string, mixed>
     */
    private array $viewConfig;

    /**
     * @param array<string, mixed> $actionConfig
     */
    public function load(array $actionConfig): ?ViewConfiguration
    {
        if (!array_key_exists('view', $actionConfig)) {
            return null;
        }

        Assert::isMap($actionConfig['view']);

        $this->viewConfig = $actionConfig['view'];

        return new ViewConfiguration(
            $this->getPath(),
            $this->getVariables(),
        );
    }

    private function getPath(): string
    {
        if (!array_key_exists('path', $this->viewConfig)) {
            throw new RuntimeException();
        }

        Assert::string($this->viewConfig['path']);

        return $this->viewConfig['path'];
    }


    private function getVariables(): KeyStringCollection
    {
        if (!array_key_exists('variables', $this->viewConfig)) {
            return KeyStringCollection::createEmpty();
        }

        Assert::isMap($this->viewConfig['variables']);
        Assert::allString($this->viewConfig['variables']);

        return new KeyStringCollection(
            $this->viewConfig['variables'],
        );
    }
}
