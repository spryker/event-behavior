<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use Spryker\Zed\EventBehavior\Business\Exception\EventResourceNotFoundException;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface;

class EventResourcePluginResolver
{
    protected const REPOSITORY_EVENT_RESOURCE_PLUGINS = 'repository';
    protected const QUERY_CONTAINER_EVENT_RESOURCE_PLUGINS = 'query_container';

    /**
     * @var array
     */
    protected $eventResourcePlugins;

    /**
     * @var \Spryker\Zed\EventBehavior\Business\Model\EventResourceRepositoryManager
     */
    protected $eventResourceRepositoryManager;

    /**
     * @var \Spryker\Zed\EventBehavior\Business\Model\EventResourceQueryContainerManager
     */
    protected $eventResourceQueryContainerManager;

    /**
     * @param \Spryker\Zed\EventBehavior\Business\Model\EventResourceRepositoryManager $eventResourceRepositoryManager
     * @param \Spryker\Zed\EventBehavior\Business\Model\EventResourceQueryContainerManager $eventResourceQueryContainerManager
     * @param array $eventResourcePlugins
     */
    public function __construct(
        EventResourceRepositoryManager $eventResourceRepositoryManager,
        EventResourceQueryContainerManager $eventResourceQueryContainerManager,
        array $eventResourcePlugins
    ) {
        $this->eventResourceRepositoryManager = $eventResourceRepositoryManager;
        $this->eventResourceQueryContainerManager = $eventResourceQueryContainerManager;
        $this->eventResourcePlugins = $eventResourcePlugins;
    }

    /**
     * @param string[] $resources
     * @param int[] $ids
     *
     * @return void
     */
    public function executeResolvedPluginsBySources(array $resources, array $ids): void
    {
        $pluginsPerExporter = $this->getResolvedPluginsByResources($resources);
        $this->eventResourceQueryContainerManager->triggerResourceEvents($pluginsPerExporter[static::QUERY_CONTAINER_EVENT_RESOURCE_PLUGINS], $ids);
        $this->eventResourceRepositoryManager->triggerResourceEvents($pluginsPerExporter[static::REPOSITORY_EVENT_RESOURCE_PLUGINS], $ids);
    }

    /**
     * @return string[]
     */
    public function getAvailableResourceNames(): array
    {
        $resourceNames = [];

        foreach ($this->eventResourcePlugins as $plugin) {
            $resourceNames[] = $plugin->getResourceName();
        }

        sort($resourceNames);

        return $resourceNames;
    }

    /**
     * @param string[] $resources
     *
     * @throws \Spryker\Zed\EventBehavior\Business\Exception\EventResourceNotFoundException
     *
     * @return \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[]
     */
    protected function getResolvedPluginsByResources(array $resources): array
    {
        $this->mapPluginsByResourceName();
        $effectivePluginsByResource = $this->getEffectivePlugins($resources);
        $pluginsPerExporter = [
            static::REPOSITORY_EVENT_RESOURCE_PLUGINS => [],
            static::QUERY_CONTAINER_EVENT_RESOURCE_PLUGINS => [],
        ];

        foreach ($effectivePluginsByResource as $effectivePlugins) {
            $pluginsPerExporter = $this->extractEffectivePlugins($effectivePlugins, $pluginsPerExporter);
        }

        return $pluginsPerExporter;
    }

    /**
     * @return void
     */
    protected function mapPluginsByResourceName(): void
    {
        $mappedDataPlugins = [];
        foreach ($this->eventResourcePlugins as $plugin) {
            $mappedDataPlugins[$plugin->getResourceName()][] = $plugin;
        }

        $this->eventResourcePlugins = $mappedDataPlugins;
    }

    /**
     * @param string[] $resources
     *
     * @throws \Spryker\Zed\EventBehavior\Business\Exception\EventResourceNotFoundException
     *
     * @return \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[]
     */
    protected function getEffectivePlugins(array $resources): array
    {
        $effectivePlugins = [];
        if (empty($resources)) {
            return $this->eventResourcePlugins;
        }

        foreach ($resources as $resource) {
            if (!isset($this->eventResourcePlugins[$resource])) {
                throw new EventResourceNotFoundException(
                    sprintf(
                        'There is no resource with the name: %s. ',
                        $resource
                    )
                );
            }

            $effectivePlugins[$resource] = $this->eventResourcePlugins[$resource];
        }

        return $effectivePlugins;
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[] $effectivePlugins
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[] $pluginsPerExporter
     *
     * @return \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[]
     */
    protected function extractEffectivePlugins($effectivePlugins, $pluginsPerExporter): array
    {
        foreach ($effectivePlugins as $effectivePlugin) {
            if ($effectivePlugin instanceof EventResourceRepositoryPluginInterface) {
                $pluginsPerExporter[static::REPOSITORY_EVENT_RESOURCE_PLUGINS][] = $effectivePlugin;
            }
            if ($effectivePlugin instanceof EventResourceQueryContainerPluginInterface) {
                $pluginsPerExporter[static::QUERY_CONTAINER_EVENT_RESOURCE_PLUGINS][] = $effectivePlugin;
            }
        }

        return $pluginsPerExporter;
    }
}
