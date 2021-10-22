<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use Spryker\Zed\EventBehavior\Business\Exception\EventResourceNotFoundException;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface;
use Spryker\Zed\EventBehavior\EventBehaviorConfig;
use Spryker\Zed\Kernel\Persistence\EntityManager\InstancePoolingTrait;

class EventResourcePluginResolver implements EventResourcePluginResolverInterface
{
    use InstancePoolingTrait;

    /**
     * @var string
     */
    protected const REPOSITORY_EVENT_RESOURCE_PLUGINS = 'repository';
    /**
     * @var string
     */
    protected const QUERY_CONTAINER_EVENT_RESOURCE_PLUGINS = 'query_container';

    /**
     * @var \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[]
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
     * @var \Spryker\Zed\EventBehavior\EventBehaviorConfig
     */
    protected $eventBehaviorConfig;

    /**
     * @param \Spryker\Zed\EventBehavior\Business\Model\EventResourceRepositoryManager $eventResourceRepositoryManager
     * @param \Spryker\Zed\EventBehavior\Business\Model\EventResourceQueryContainerManager $eventResourceQueryContainerManager
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[] $eventResourcePlugins
     * @param \Spryker\Zed\EventBehavior\EventBehaviorConfig $eventBehaviorConfig
     */
    public function __construct(
        EventResourceRepositoryManager $eventResourceRepositoryManager,
        EventResourceQueryContainerManager $eventResourceQueryContainerManager,
        array $eventResourcePlugins,
        EventBehaviorConfig $eventBehaviorConfig
    ) {
        $this->eventResourceRepositoryManager = $eventResourceRepositoryManager;
        $this->eventResourceQueryContainerManager = $eventResourceQueryContainerManager;
        $this->eventResourcePlugins = $eventResourcePlugins;
        $this->eventBehaviorConfig = $eventBehaviorConfig;
    }

    /**
     * @param string[] $resources
     * @param (string|int)[] $ids
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[] $resourcePublisherPlugins
     *
     * @return void
     */
    public function executeResolvedPluginsBySources(array $resources, array $ids = [], array $resourcePublisherPlugins = []): void
    {
        $pluginsPerExporter = $this->getResolvedPluginsByResources($resources, $resourcePublisherPlugins);

        $this->processResourceEvents($pluginsPerExporter, $ids);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[] $resourcePublisherPlugins
     *
     * @return string[]
     */
    public function getAvailableResourceNames(array $resourcePublisherPlugins = []): array
    {
        $resourceNames = [];

        foreach (array_merge($this->eventResourcePlugins, $resourcePublisherPlugins) as $plugin) {
            $resourceNames[] = $plugin->getResourceName();
        }

        sort($resourceNames);

        return array_unique($resourceNames);
    }

    /**
     * @param string[] $resources
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[] $resourcePublisherPlugins
     *
     * @return \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[][]
     */
    protected function getResolvedPluginsByResources(array $resources, array $resourcePublisherPlugins = []): array
    {
        $mappedEventResourcePlugin = $this->mapPluginsByResourceNameAndEvent($resourcePublisherPlugins);
        $effectivePluginsByResource = $this->filterPluginsByResources($mappedEventResourcePlugin, $resources);
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
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[][] $pluginsPerExporter
     * @param (string|int)[] $ids
     *
     * @return void
     */
    protected function processResourceEvents(array $pluginsPerExporter, array $ids): void
    {
        $isPoolingStateChanged = $this->disableInstancePooling();

        $this->eventResourceQueryContainerManager->processResourceEvents($pluginsPerExporter[static::QUERY_CONTAINER_EVENT_RESOURCE_PLUGINS], $ids);
        $this->eventResourceRepositoryManager->processResourceEvents($pluginsPerExporter[static::REPOSITORY_EVENT_RESOURCE_PLUGINS], $ids);

        if ($isPoolingStateChanged) {
            $this->enableInstancePooling();
        }
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[] $resourcePublisherPlugins
     *
     * @return \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[][][]
     */
    protected function mapPluginsByResourceNameAndEvent(array $resourcePublisherPlugins = []): array
    {
        $mappedEventResourcePlugin = [];

        if ($resourcePublisherPlugins) {
            $this->eventResourcePlugins = array_merge($this->eventResourcePlugins, $resourcePublisherPlugins);
        }

        foreach ($this->eventResourcePlugins as $plugin) {
            $mappedEventResourcePlugin[$plugin->getResourceName()][$plugin->getEventName()][] = $plugin;
        }

        return $mappedEventResourcePlugin;
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[][][] $eventResourcePlugins
     * @param string[] $resources
     *
     * @throws \Spryker\Zed\EventBehavior\Business\Exception\EventResourceNotFoundException
     *
     * @return \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[][][]
     */
    protected function filterPluginsByResources(array $eventResourcePlugins, array $resources): array
    {
        if ($resources === []) {
            return $eventResourcePlugins;
        }

        $effectivePlugins = [];
        foreach ($resources as $resource) {
            if (!isset($eventResourcePlugins[$resource])) {
                throw new EventResourceNotFoundException(
                    sprintf(
                        'There is no resource with the name: %s.',
                        $resource
                    )
                );
            }

            $effectivePlugins[$resource] = $eventResourcePlugins[$resource];
        }

        return $effectivePlugins;
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[][] $effectivePlugins
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[][] $pluginsPerExporter
     *
     * @return \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[][]
     */
    protected function extractEffectivePlugins($effectivePlugins, $pluginsPerExporter): array
    {
        foreach ($effectivePlugins as $effectiveEventPlugins) {
            $effectivePlugin = $this->findEffectivePlugin($effectiveEventPlugins);

            if ($effectivePlugin === null) {
                continue;
            }

            if ($this->isEventResourceRepositoryPlugin($effectivePlugin)) {
                $pluginsPerExporter[static::REPOSITORY_EVENT_RESOURCE_PLUGINS][] = $effectivePlugin;
            }

            if ($this->isEventResourceQueryContainerPlugin($effectivePlugin)) {
                $pluginsPerExporter[static::QUERY_CONTAINER_EVENT_RESOURCE_PLUGINS][] = $effectivePlugin;
            }
        }

        return $pluginsPerExporter;
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[] $eventResourcePlugins
     *
     * @return \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface|null
     */
    protected function findEffectivePlugin(array $eventResourcePlugins): ?EventResourcePluginInterface
    {
        if ($eventResourcePlugins === []) {
            return null;
        }

        return current($eventResourcePlugins);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $eventResourcePlugin
     *
     * @return bool
     */
    protected function isEventResourceRepositoryPlugin(EventResourcePluginInterface $eventResourcePlugin): bool
    {
        return $eventResourcePlugin instanceof EventResourceRepositoryPluginInterface || $eventResourcePlugin instanceof EventResourceBulkRepositoryPluginInterface;
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $eventResourcePlugin
     *
     * @return bool
     */
    protected function isEventResourceQueryContainerPlugin(EventResourcePluginInterface $eventResourcePlugin): bool
    {
        return $eventResourcePlugin instanceof EventResourceQueryContainerPluginInterface;
    }
}
