<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use Generated\Shared\Transfer\EventEntityTransfer;
use Iterator;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface;

class EventResourceQueryContainerManager implements EventResourceManagerInterface
{
    protected const DEFAULT_CHUNK_SIZE = 100;

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
     * @param int|null $chunkSize
     */
    public function __construct(
        EventBehaviorToEventInterface $eventFacade,
        array $eventResourcePlugins,
        ?int $chunkSize = null
    ) {
        $this->eventFacade = $eventFacade;
        $this->eventResourcePlugins = $eventResourcePlugins;
        $this->chunkSize = $chunkSize ?? static::DEFAULT_CHUNK_SIZE;
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface[] $plugins
     * @param array $ids
     *
     * @return void
     */
    public function triggerResourceEvents(array $plugins, array $ids = []): void
    {
        foreach ($plugins as $plugin) {
            $this->triggerEvents($plugin, $ids);
        }
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface $plugin
     * @param array $ids
     *
     * @return void
     */
    protected function triggerEvents(EventResourceQueryContainerPluginInterface $plugin, array $ids = []): void
    {
        if ($ids) {
            $this->trigger($plugin, $ids);

            return;
        }

        if ($plugin->queryData($ids) === null) {
            $this->triggerPluginOnce($plugin);

            return;
        }

        $this->triggerEventsAll($plugin);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface $plugin
     *
     * @return void
     */
    protected function triggerPluginOnce(EventResourceQueryContainerPluginInterface $plugin): void
    {
        $this->eventFacade->trigger($plugin->getEventName(), new EventEntityTransfer());
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface $plugin
     *
     * @return void
     */
    protected function triggerEventsAll(EventResourceQueryContainerPluginInterface $plugin): void
    {
        foreach ($this->createEventResourceQueryContainerPluginIterator($plugin) as $ids) {
            $this->trigger($plugin, $ids);
        }
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     *
     * @return \Iterator
     */
    protected function createEventResourceQueryContainerPluginIterator(EventResourcePluginInterface $plugin): Iterator
    {
        return new EventResourceQueryContainerPluginIterator($plugin, $this->chunkSize);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     * @param array $ids
     *
     * @return void
     */
    protected function trigger(EventResourcePluginInterface $plugin, array $ids): void
    {
        $eventEntityTransfers = array_map(function ($id) {
            return (new EventEntityTransfer())->setId($id);
        }, $ids);

        $this->eventFacade->triggerBulk($plugin->getEventName(), $eventEntityTransfers);
    }
}
