<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use Generated\Shared\Transfer\EventEntityTransfer;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface;

class EventResourceRepositoryManager implements EventResourceManagerInterface
{
    protected const ID_NULL = null;
    protected const DELIMITER = '.';

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
     * @param array $eventResourcePlugins
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
     * @param array $plugins
     * @param array $ids
     *
     * @return void
     */
    public function triggerResourceEvents(array $plugins, array $ids = [])
    {
        foreach ($plugins as $plugin) {
            $this->triggerEvents($plugin, $ids);
        }
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface $plugin
     * @param array $ids
     *
     * @return void
     */
    protected function triggerEvents(EventResourceRepositoryPluginInterface $plugin, array $ids = [])
    {
        if ($ids) {
            $this->trigger($plugin, $ids);

            return;
        }

        if (!$plugin->getData()) {
            $this->trigger($plugin, [static::ID_NULL]);

            return;
        }

        $this->triggerEventsAll($plugin);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface $plugin
     *
     * @return void
     */
    protected function triggerEventsAll(EventResourceRepositoryPluginInterface $plugin): void
    {
        $eventEntities = $plugin->getData();
        $count = count($eventEntities);
        $loops = $count / $this->chunkSize;
        $offset = 0;

        for ($i = 0; $i < $loops; $i++) {
            $chunkOfEventEntitiesTransfers = array_slice($eventEntities, $offset, $this->chunkSize);
            $eventEntitiesIds = $this->getEventEntitiesIds($plugin, $chunkOfEventEntitiesTransfers);
            $this->trigger($plugin, $eventEntitiesIds);
            $offset += $this->chunkSize;
        }
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     * @param array $chunkOfEventEntitiesTransfers
     *
     * @return array
     */
    protected function getEventEntitiesIds($plugin, $chunkOfEventEntitiesTransfers)
    {
        $eventEntitiesIds = [];

        foreach($chunkOfEventEntitiesTransfers as $entitiesTransfer) {
            $entitiesTransferArray = $entitiesTransfer->modifiedToArray();
            $idColumnName = $this->getIdColumnName($plugin);
            $eventEntitiesIds[] = $entitiesTransferArray[$idColumnName];
        }

        return $eventEntitiesIds;
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     *
     * @return null|string
     */
    protected function getIdColumnName($plugin)
    {
        $idColumnName = explode(static::DELIMITER, $plugin->getIdColumnName());
        return $idColumnName[1] ?? null;
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
}
