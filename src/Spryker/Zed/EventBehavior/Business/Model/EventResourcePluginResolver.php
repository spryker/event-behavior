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

class EventResourcePluginResolver implements EventResourcePluginResolverInterface
{
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
        $effectivePlugins = $this->getEffectivePlugins($resources);
        $filteredEffectivePlugins = $this->removeDuplicatePluginsByResourceAndEventName($effectivePlugins);

        $this->eventResourceQueryContainerManager->triggerResourceEvents($this->getQueryContainerResourcePlugins($filteredEffectivePlugins), $ids);
        $this->eventResourceRepositoryManager->triggerResourceEvents($this->getRepositoryResourcePlugins($filteredEffectivePlugins), $ids);
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

        return array_unique($resourceNames);
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
        if (empty($resources)) {
            return $this->eventResourcePlugins;
        }

        $filteredPlugins = array_filter($this->eventResourcePlugins, function (EventResourcePluginInterface $eventResourcePlugin) use ($resources) {
            return in_array($eventResourcePlugin->getResourceName(), $resources);
        });

        foreach ($resources as $resource) {
            if (!array_filter($filteredPlugins, function (EventResourcePluginInterface $eventResourcePlugin) use ($resource) {
                return $eventResourcePlugin->getResourceName() === $resource;
            })) {
                throw new EventResourceNotFoundException(
                    sprintf(
                        'There is no resource with the name: %s. ',
                        $resource
                    )
                );
            }
        }

        return $filteredPlugins;
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $eventResourcePlugin
     *
     * @return bool
     */
    protected function isRepositoryPlugin(EventResourcePluginInterface $eventResourcePlugin): bool
    {
        return $eventResourcePlugin instanceof EventResourceRepositoryPluginInterface || $eventResourcePlugin instanceof EventResourceBulkRepositoryPluginInterface;
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $eventResourcePlugin
     *
     * @return bool
     */
    protected function isQueryContainerPlugin(EventResourcePluginInterface $eventResourcePlugin): bool
    {
        return $eventResourcePlugin instanceof EventResourceQueryContainerPluginInterface;
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $eventResourcePlugin
     *
     * @return bool
     */
    protected function uniqueResourceAndEventNamesCallback(EventResourcePluginInterface $eventResourcePlugin): bool
    {
        static $resourceEventList = [];
        $key = $eventResourcePlugin->getResourceName() . $eventResourcePlugin->getEventName();

        if (in_array($key, $resourceEventList)) {
            return false;
        }

        $resourceEventList[] = $key;

        return true;
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[] $eventResourcePlugins
     *
     * @return array
     */
    protected function removeDuplicatePluginsByResourceAndEventName(array $eventResourcePlugins): array
    {
        return array_filter($eventResourcePlugins, [$this, 'uniqueResourceAndEventNamesCallback']);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[] $eventResourcePlugins
     *
     * @return array
     */
    protected function getQueryContainerResourcePlugins(array $eventResourcePlugins): array
    {
        return array_filter($eventResourcePlugins, [$this, 'isQueryContainerPlugin']);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[] $eventResourcePlugins
     *
     * @return array
     */
    protected function getRepositoryResourcePlugins(array $eventResourcePlugins): array
    {
        return array_filter($eventResourcePlugins, [$this, 'isRepositoryPlugin']);
    }
}
