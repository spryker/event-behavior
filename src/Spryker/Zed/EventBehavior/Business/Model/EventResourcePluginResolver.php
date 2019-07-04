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
        $filteredEffectivePlugins = array_filter($effectivePlugins, [$this, 'uniqueResourceAndEventNamesCallback']);

        $this->eventResourceQueryContainerManager->triggerResourceEvents(array_filter($filteredEffectivePlugins, [$this, 'isQueryContainerPlugin']), $ids);
        $this->eventResourceRepositoryManager->triggerResourceEvents(array_filter($filteredEffectivePlugins, [$this, 'isRepositoryPlugin']), $ids);
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

        $filteredPlugins = array_filter($this->eventResourcePlugins, function (EventResourcePluginInterface $plugin) use ($resources) {
            return in_array($plugin->getResourceName(), $resources);
        });

        if (!$filteredPlugins) {
            throw new EventResourceNotFoundException(
                sprintf(
                    'There are no registered event resource plugins for resources: %s.',
                    implode(', ', $resources)
                )
            );
        }

        return $filteredPlugins;
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     *
     * @return bool
     */
    protected function isRepositoryPlugin(EventResourcePluginInterface $plugin): bool
    {
        return ($plugin instanceof EventResourceRepositoryPluginInterface || $plugin instanceof EventResourceBulkRepositoryPluginInterface);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     *
     * @return bool
     */
    protected function isQueryContainerPlugin(EventResourcePluginInterface $plugin): bool
    {
        return ($plugin instanceof EventResourceQueryContainerPluginInterface);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     *
     * @return bool
     */
    protected function uniqueResourceAndEventNamesCallback(EventResourcePluginInterface $plugin): bool
    {
        static $eventList = [];
        $key = $plugin->getResourceName() . $plugin->getEventName();

        if (in_array($key, $eventList)) {
            return false;
        }

        $eventList[] = $key;

        return true;
    }
}
