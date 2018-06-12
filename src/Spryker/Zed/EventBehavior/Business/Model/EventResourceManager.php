<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use Generated\Shared\Transfer\EventEntityTransfer;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface;

class EventResourceManager implements EventResourceManagerInterface
{
    const ID_NULL = null;

    /**
     * @var \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface
     */
    protected $eventFacade;

    /**
     * @var \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[]
     */
    protected $eventResourcePlugins;

    /**
     * @var int
     */
    protected $chunkSize;

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface $eventFacade
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[] $eventResourcePlugins
     * @param int $chunkSize
     */
    public function __construct(
        EventBehaviorToEventInterface $eventFacade,
        array $eventResourcePlugins,
        $chunkSize = 100
    ) {
        $this->eventFacade = $eventFacade;
        $this->eventResourcePlugins = $eventResourcePlugins;
        $this->chunkSize = $chunkSize;
    }

    /**
     * @param array $resources
     * @param array $ids
     *
     * @return void
     */
    public function triggerResourceEvents(array $resources, array $ids = [])
    {
        $this->mapPluginsByResourceName();
        $plugins = $this->getEffectivePlugins($resources);

        foreach ($plugins as $plugin) {
            $this->triggerEvents($plugin, $ids);
        }
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     * @param array $ids
     *
     * @return void
     */
    protected function triggerEvents(EventResourcePluginInterface $plugin, array $ids = [])
    {
        if ($ids) {
            $this->trigger($plugin, $ids);

            return;
        }

        if (!$plugin->queryData()) {
            $this->trigger($plugin, [static::ID_NULL]);

            return;
        }

        $this->triggerEventsAll($plugin);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     *
     * @return void
     */
    protected function triggerEventsAll(EventResourcePluginInterface $plugin): void
    {
        $query = $plugin->queryData();
        $count = $query->count();
        $loops = $count / $this->chunkSize;
        $offset = 0;

        for ($i = 0; $i < $loops; $i++) {
            $ids = $plugin->queryData()
                ->offset($offset)
                ->limit($this->chunkSize)
                ->select([$plugin->getIdColumnName()])
                ->find()
                ->getData();

            $this->trigger($plugin, $ids);
            $offset += $this->chunkSize;
        }
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     * @param array $ids
     *
     * @return void
     */
    protected function trigger(EventResourcePluginInterface $plugin, array $ids): void
    {
        //TODO replace this with triggerBulk as soon this method is available in Event module
        foreach ($ids as $id) {
            $eventEntityTransfer = (new EventEntityTransfer())->setId($id);
            $this->eventFacade->trigger($plugin->getEventName(), $eventEntityTransfer);
        }
    }

    /**
     * @return void
     */
    protected function mapPluginsByResourceName(): void
    {
        $mappedDataPlugins = [];
        foreach ($this->eventResourcePlugins as $plugin) {
            $mappedDataPlugins[$plugin->getResourceName()] = $plugin;
        }

        $this->eventResourcePlugins = $mappedDataPlugins;
    }

    /**
     * @param array $resources
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
            if (isset($this->eventResourcePlugins[$resource])) {
                $effectivePlugins[$resource] = $this->eventResourcePlugins[$resource];
            }
        }

        return $effectivePlugins;
    }
}
