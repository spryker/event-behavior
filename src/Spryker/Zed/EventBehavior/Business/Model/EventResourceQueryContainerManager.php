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
    protected const ID_NULL = null;
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
     * @param array $plugins
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

        if (!$plugin->queryData($ids)) {
            $this->trigger($plugin, [static::ID_NULL]);

            return;
        }

        $this->triggerEventsAll($plugin);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface $plugin
     *
     * @return void
     */
    protected function triggerEventsAll(EventResourceQueryContainerPluginInterface $plugin): void
    {
        foreach ($this->createEventResourceQueryContainerPluginIterator() as $ids) {
            $this->trigger($plugin, $ids);
        }
    }

    /**
     * @return \Iterator
     */
    protected function createEventResourceQueryContainerPluginIterator(): Iterator
    {
        return new EventResourceQueryContainerPluginIterator($plugin, static::DEFAULT_CHUNK_SIZE);
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
